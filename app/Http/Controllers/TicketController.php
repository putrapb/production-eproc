<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketDocumentRequest;
use App\Models\ApprovalLog;
use App\Models\Budget;
use App\Models\Ticket;
use App\Services\SmartValidationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function __construct(private SmartValidationService $smartValidation) {}

    /**
     * Display role-filtered ticket list.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $tickets = Ticket::with(['user', 'approvalLogs.user'])
            ->forRole($user)
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('item_name', 'like', "%{$s}%")
                  ->orWhere('vendor_name', 'like', "%{$s}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('tickets.index', compact('tickets', 'user'));
    }

    /**
     * Display detailed ticket information with approval log.
     */
    public function show(Ticket $ticket, Request $request): View
    {
        $user = $request->user();

        // Ensure the user has access to this ticket based on their role
        $this->authorizeView($ticket, $user);

        $ticket->load(['user', 'approvalLogs.user']);

        return view('tickets.show', compact('ticket', 'user'));
    }

    /**
     * Show form for creating a new ticket. (Requester only)
     */
    public function create(): View
    {
        return view('tickets.create');
    }

    /**
     * Store a new ticket. (Requester only)
     */
    public function store(StoreTicketRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Upload izin prinsip PDF to local public storage
        $folder  = config('eprocurement.storage.izin_prinsip_folder', 'izin_prinsip');
        $path    = $request->file('izin_prinsip')->store($folder, 'public');

        $ticket = Ticket::create([
            'user_id'     => $user->id,
            'title'       => $request->title,
            'item_name'   => $request->item_name,
            'category'    => $request->category,
            'description' => $request->description,
            'quantity'    => $request->quantity,
            'vendor_name' => $request->vendor_name,
            'amount'      => $request->amount,
            'document_path' => $path,
            'status'      => Ticket::STATUS_PENDING_REVIEW,
        ]);

        // Log the submission
        ApprovalLog::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'action'    => ApprovalLog::ACTION_SUBMITTED,
        ]);

        return redirect()->route('tickets.index')
            ->with('success', 'Tiket pengadaan berhasil diajukan.');
    }

    /**
     * Show revision form for re-uploading document. (Requester, status: revision)
     */
    public function edit(Ticket $ticket, Request $request): View|RedirectResponse
    {
        if (auth()->user()->isRequester()) {
            abort_if($ticket->user_id !== auth()->id(), 403);
        }

        $this->ensureStatus($ticket, Ticket::STATUS_REVISION);

        return view('tickets.edit', compact('ticket'));
    }

    /**
     * Handle document re-upload for revision. (Requester, status: revision)
     */
    public function update(UpdateTicketDocumentRequest $request, Ticket $ticket): RedirectResponse
    {
        if (auth()->user()->isRequester()) {
            abort_if($ticket->user_id !== auth()->id(), 403);
        }

        $folder = config('eprocurement.storage.izin_prinsip_folder', 'izin_prinsip');

        // Delete old document from storage if exists
        if ($ticket->document_path) {
            Storage::disk('public')->delete($ticket->document_path);
        }

        $path = $request->file('izin_prinsip')->store($folder, 'public');

        $ticket->update([
            'document_path' => $path,
            'status'        => Ticket::STATUS_PENDING_REVIEW,
        ]);

        ApprovalLog::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $request->user()->id,
            'action'    => ApprovalLog::ACTION_REVISED,
            'notes'     => 'Dokumen izin prinsip diunggah ulang oleh Requester.',
        ]);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Dokumen berhasil diunggah ulang. Tiket kembali ke status Pending Review.');
    }

    /**
     * PFA: Review the izin prinsip document — accept or reject.
     */
    public function review(Request $request, Ticket $ticket): RedirectResponse
    {
        $request->validate([
            'action' => ['required', 'in:accept,reject'],
            'notes'  => ['nullable', 'string', 'max:1000'],
        ]);

        $this->ensureStatus($ticket, Ticket::STATUS_PENDING_REVIEW);

        if ($request->action === 'accept') {
            $ticket->update(['status' => Ticket::STATUS_NEED_TO_VALIDATE]);
            $action  = ApprovalLog::ACTION_FOLLOWED_UP;
            $message = 'Dokumen diterima. Silakan jalankan Smart Validation untuk mengklasifikasikan anggaran.';
        } else {
            $ticket->update(['status' => Ticket::STATUS_REVISION]);
            $action  = ApprovalLog::ACTION_REJECTED_DOCUMENT;
            $message = 'Dokumen ditolak. Tiket dikembalikan ke Requester untuk revisi.';
        }

        ApprovalLog::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $request->user()->id,
            'action'    => $action,
            'notes'     => $request->notes,
        ]);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', $message);
    }

    /**
     * Requester: Run Smart Validation (4-Gate Engine) after PFA accepts the document.
     */
    public function runSmartValidation(Request $request, Ticket $ticket): RedirectResponse
    {
        if (auth()->user()->isRequester()) {
            abort_if($ticket->user_id !== auth()->id(), 403);
        }

        $this->ensureStatus($ticket, Ticket::STATUS_NEED_TO_VALIDATE);

        $user   = $request->user();
        $result = $this->smartValidation->run($ticket, $user);

        if ($result['success']) {
            return redirect()->route('tickets.show', $ticket)
                ->with('success', $result['message']);
        }

        if ($result['over_budget']) {
            // Pass over-budget info to session for the cross-fund popup in view
            return redirect()->route('tickets.show', $ticket)
                ->with('over_budget', true)
                ->with('classified_type', $result['classified_type'])
                ->with('available_balance', $result['available_balance'])
                ->with('warning', $result['message']);
        }

        return redirect()->route('tickets.show', $ticket)
            ->with('error', "Gate {$result['gate']}: {$result['message']}");
    }

    /**
     * Requester: Confirm cross-fund (silang dana) request after Gate 4 over-budget.
     */
    public function applyCrossFund(Request $request, Ticket $ticket): RedirectResponse
    {
        if (auth()->user()->isRequester()) {
            abort_if($ticket->user_id !== auth()->id(), 403);
        }

        $this->ensureStatus($ticket, Ticket::STATUS_NEED_TO_VALIDATE);

        $user   = $request->user();
        $result = $this->smartValidation->applyCrossFund($ticket, $user);

        if ($result['success']) {
            return redirect()->route('tickets.show', $ticket)
                ->with('success', $result['message']);
        }

        return redirect()->route('tickets.show', $ticket)
            ->with('error', $result['message']);
    }

    /**
     * Requester: Cancel (drop) ticket when it is in need_to_validate status.
     */
    public function cancel(Request $request, Ticket $ticket): RedirectResponse
    {
        if (auth()->user()->isRequester()) {
            abort_if($ticket->user_id !== auth()->id(), 403);
        }

        $this->ensureStatus($ticket, Ticket::STATUS_NEED_TO_VALIDATE);

        $ticket->update(['status' => Ticket::STATUS_DECLINED]);

        ApprovalLog::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $request->user()->id,
            'action'    => 'declined',
            'notes'     => $request->notes ?? 'Dibatalkan oleh Requester karena tidak mengajukan silang dana / hasil peninjauan internal.',
        ]);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Tiket pengadaan berhasil dibatalkan (Declined).');
    }

    /**
     * Department Head: Forward ticket to Division Head.
     */
    public function forward(Request $request, Ticket $ticket): RedirectResponse
    {
        $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->ensureStatus($ticket, Ticket::STATUS_PENDING_DEPT_HEAD);

        $ticket->update(['status' => Ticket::STATUS_PENDING_DIV_HEAD]);

        ApprovalLog::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $request->user()->id,
            'action'    => ApprovalLog::ACTION_FORWARDED,
            'notes'     => $request->notes,
        ]);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Tiket berhasil diteruskan ke Division Head.');
    }

    /**
     * Division Head: Final approve or decline decision.
     */
    public function decide(Request $request, Ticket $ticket): RedirectResponse
    {
        $request->validate([
            'action' => ['required', 'in:approve,decline'],
            'notes'  => ['nullable', 'string', 'max:1000'],
        ]);

        $this->ensureStatus($ticket, Ticket::STATUS_PENDING_DIV_HEAD);

        $user = $request->user();

        $message = DB::transaction(function () use ($request, $ticket, $user) {
            if ($request->action === 'approve') {
                // Release temporary lock, apply permanent deduction
                $budget = Budget::findForTicket(
                    $ticket->expenditure_type,
                    $ticket->category,
                    now()->year
                );

                if ($budget) {
                    $budget->permanentDeduct((float) $ticket->amount);
                }

                $ticket->update(['status' => Ticket::STATUS_APPROVED]);

                ApprovalLog::create([
                    'ticket_id' => $ticket->id,
                    'user_id'   => $user->id,
                    'action'    => ApprovalLog::ACTION_APPROVED,
                    'notes'     => $request->notes,
                ]);

                return 'Pengadaan disetujui. PFA dapat menerbitkan Purchase Order.';
            } else {
                // Release temporary lock — no permanent deduction
                $budget = Budget::findForTicket(
                    $ticket->expenditure_type,
                    $ticket->category,
                    now()->year
                );

                if ($budget) {
                    $budget->unlock((float) $ticket->amount);
                }

                $ticket->update(['status' => Ticket::STATUS_DECLINED]);

                ApprovalLog::create([
                    'ticket_id' => $ticket->id,
                    'user_id'   => $user->id,
                    'action'    => ApprovalLog::ACTION_DECLINED,
                    'notes'     => $request->notes,
                ]);

                return 'Pengadaan ditolak. Saldo anggaran dilepas.';
            }
        });

        return redirect()->route('tickets.show', $ticket)
            ->with('success', $message);
    }

    /**
     * Stream the Izin Prinsip document inline (for PDF preview).
     */
    public function streamDocument(Ticket $ticket, Request $request)
    {
        $this->authorizeView($ticket, $request->user());

        if (!$ticket->document_path || !Storage::disk('public')->exists($ticket->document_path)) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        $path = Storage::disk('public')->path($ticket->document_path);

        if ($request->query('download')) {
            return response()->download($path, 'Izin_Prinsip_' . $ticket->id . '.pdf');
        }

        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="izin-prinsip.pdf"'
        ];

        return response()->file($path, $headers);
    }

    // ──────────────────────────────────────────────────────────
    // Private Helpers
    // ──────────────────────────────────────────────────────────

    private function ensureStatus(Ticket $ticket, string $expectedStatus): void
    {
        if ($ticket->status !== $expectedStatus) {
            $statusLabels = [
                'pending_review'   => 'Menunggu Tinjauan PFA',
                'need_to_validate' => 'Menunggu Smart Validation',
                'pending_dept_head'=> 'Menunggu Department Head',
                'pending_div_head' => 'Menunggu Division Head',
                'approved'         => 'Disetujui',
                'declined'         => 'Ditolak',
                'revision'         => 'Perlu Revisi',
                'po_generated'     => 'PO Diterbitkan',
            ];
            $label = $statusLabels[$ticket->status] ?? $ticket->status;

            redirect()->route('tickets.show', $ticket)
                ->with('error', "Aksi tidak dapat dilakukan. Status tiket saat ini adalah: {$label}.")
                ->throwResponse();
        }
    }

    private function authorizeView(Ticket $ticket, $user): void
    {
        // Requester can only see their own tickets
        if ($user->isRequester() && $ticket->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke tiket ini.');
        }
    }
}
