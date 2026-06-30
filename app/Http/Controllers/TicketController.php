<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketDocumentRequest;
use App\Models\ApprovalLog;
use App\Models\Budget;
use App\Models\Notification;
use App\Models\Ticket;
use App\Models\TicketDocument;
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
        $user    = $request->user();
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100])
            ? (int) $request->per_page
            : 15;

        $tickets = Ticket::with(['user', 'approvalLogs.user'])
            ->forRole($user)
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('item_name', 'like', "%{$s}%")
                  ->orWhere('vendor_name', 'like', "%{$s}%");
            }))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('tickets.index', compact('tickets', 'user', 'perPage'));
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

        // 1. Create the ticket first
        $ticket = Ticket::create([
            'user_id'     => $user->id,
            'title'       => $request->title,
            'item_name'   => $request->item_name,
            'category'    => $request->category,
            'description' => $request->description,
            'pic_name'    => $request->pic_name,
            'quantity'    => $request->quantity,
            'vendor_name' => $request->vendor_name,
            'amount'      => $request->amount,
            'status'      => Ticket::STATUS_PENDING_REVIEW,
        ]);

        // 2. Upload and save each document
        $folder = config('eprocurement.storage.izin_prinsip_folder', 'izin_prinsip');
        $firstPath = null;

        if ($request->hasFile('document_files')) {
            foreach ($request->file('document_files') as $index => $file) {
                $description = $request->document_descriptions[$index] ?? 'Dokumen Pendukung';
                $path = $file->store($folder, 'public');

                if ($index === 0) {
                    $firstPath = $path;
                }

                TicketDocument::create([
                    'ticket_id'   => $ticket->id,
                    'file_path'   => $path,
                    'description' => $description,
                    'status'      => 'pending',
                ]);
            }
        }

        // Save the first path to the legacy column as a fallback
        if ($firstPath) {
            $ticket->update(['document_path' => $firstPath]);
        }

        // Log the submission
        ApprovalLog::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'action'    => ApprovalLog::ACTION_SUBMITTED,
        ]);

        // Notify all Team Leaders about new submission (they are now responsible for doc review)
        Notification::notifyRole(
            'team_leader',
            'ticket_submitted',
            'Pengajuan Baru Masuk',
            "{$user->name} mengajukan tiket: {$ticket->title}",
            $ticket->id
        );

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

        // 1. Process replacements for existing rejected documents
        if ($request->hasFile('document_files')) {
            foreach ($request->file('document_files') as $docId => $file) {
                $doc = TicketDocument::where('ticket_id', $ticket->id)->find($docId);
                if ($doc && ($doc->isRejected() || $doc->isPending())) {
                    // Delete old file
                    Storage::disk('public')->delete($doc->file_path);

                    // Store new file
                    $path = $file->store($folder, 'public');

                    // Update document
                    $doc->update([
                        'file_path' => $path,
                        'status'    => 'pending',
                        'feedback'  => null,
                    ]);
                }
            }
        }

        // 2. Process any brand new documents added during revision
        if ($request->hasFile('new_document_files')) {
            foreach ($request->file('new_document_files') as $index => $file) {
                $description = $request->new_document_descriptions[$index] ?? 'Dokumen Pendukung Baru';
                $path = $file->store($folder, 'public');

                TicketDocument::create([
                    'ticket_id'   => $ticket->id,
                    'file_path'   => $path,
                    'description' => $description,
                    'status'      => 'pending',
                ]);
            }
        }

        // Update legacy fallback column if needed
        $firstDoc = $ticket->documents()->first();
        if ($firstDoc) {
            $ticket->update(['document_path' => $firstDoc->file_path]);
        }

        // Reset ticket status to pending_review
        $ticket->update([
            'status' => Ticket::STATUS_PENDING_REVIEW,
        ]);

        ApprovalLog::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $request->user()->id,
            'action'    => ApprovalLog::ACTION_REVISED,
            'notes'     => 'Dokumen pendukung diunggah ulang/ditambahkan oleh Requester.',
        ]);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Dokumen berhasil diperbarui. Tiket kembali ke status Pending Review.');
    }

    /**
     * PFA: Review the izin prinsip document — accept or reject.
     */
    public function review(Request $request, Ticket $ticket): RedirectResponse
    {
        $request->validate([
            'document_status'     => ['required', 'array'],
            'document_status.*'   => ['required', 'in:accepted,rejected'],
            'document_feedback'   => ['nullable', 'array'],
            'document_feedback.*' => ['nullable', 'string', 'max:1000'],
            'notes'               => ['nullable', 'string', 'max:1000'],
        ]);

        $this->ensureStatus($ticket, Ticket::STATUS_PENDING_REVIEW);

        $hasRejected = false;
        $rejectedNames = [];

        DB::transaction(function () use ($request, $ticket, &$hasRejected, &$rejectedNames) {
            foreach ($ticket->documents as $doc) {
                $status = $request->document_status[$doc->id] ?? 'accepted';
                $feedback = $request->document_feedback[$doc->id] ?? null;

                if ($status === 'rejected') {
                    $hasRejected = true;
                    $rejectedNames[] = $doc->description;
                }

                $doc->update([
                    'status'   => $status,
                    'feedback' => $status === 'rejected' ? $feedback : null,
                ]);
            }

            if ($hasRejected) {
                $ticket->update(['status' => Ticket::STATUS_REVISION]);
            } else {
                $ticket->update(['status' => Ticket::STATUS_NEED_TO_VALIDATE]);
            }
        });

        if ($hasRejected) {
            $action  = ApprovalLog::ACTION_REJECTED_DOCUMENT;
            $message = 'Beberapa dokumen memerlukan revisi. Tiket dikembalikan ke Requester.';

            Notification::notify(
                $ticket->user_id,
                'ticket_revised',
                'Dokumen Perlu Revisi',
                "Dokumen berikut perlu direvisi: " . implode(', ', $rejectedNames) . ". Catatan: " . ($request->notes ?? 'Silakan periksa detail tiket.'),
                $ticket->id
            );
        } else {
            $action  = ApprovalLog::ACTION_FOLLOWED_UP;
            $message = 'Semua dokumen diterima. Silakan jalankan Smart Validation untuk mengklasifikasikan anggaran.';

            Notification::notify(
                $ticket->user_id,
                'ticket_reviewed',
                'Dokumen Diterima',
                "Dokumen tiket \"{$ticket->title}\" diterima oleh Team Leader. Silakan jalankan Smart Validation.",
                $ticket->id
            );
        }

        ApprovalLog::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $request->user()->id,
            'action'    => $action,
            'notes'     => $request->notes ?? ($hasRejected ? 'Beberapa dokumen ditolak.' : 'Semua dokumen disetujui.'),
        ]);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', $message);
    }

    /**
     * Requester: Run Smart Validation (4-Gate Engine) after PFA accepts the document.
     * Supports soft-warning confirmations for Gate 1 (duplicate) and Gate 2 (nominal).
     */
    public function runSmartValidation(Request $request, Ticket $ticket): RedirectResponse
    {
        if (auth()->user()->isRequester()) {
            abort_if($ticket->user_id !== auth()->id(), 403);
        }

        $this->ensureStatus($ticket, Ticket::STATUS_NEED_TO_VALIDATE);

        $user   = $request->user();
        $result = $this->smartValidation->run(
            $ticket,
            $user,
            duplicateConfirmed: (bool) $request->boolean('duplicate_confirmed'),
            nominalConfirmed: (bool) $request->boolean('nominal_confirmed'),
        );

        if ($result['success']) {
            // Notify all Department Heads that a ticket is ready for their decision
            Notification::notifyRole(
                'department_head',
                'ticket_validated',
                'Tiket Siap Disetujui',
                "Tiket \"{$ticket->title}\" telah tervalidasi dan menunggu keputusan Department Head.",
                $ticket->id
            );
            return redirect()->route('tickets.show', $ticket)
                ->with('success', $result['message']);
        }

        // Gate 1: Duplicate soft warning — show confirmation popup
        if ($result['needs_duplicate_confirmation'] ?? false) {
            return redirect()->route('tickets.show', $ticket)
                ->with('needs_duplicate_confirmation', true)
                ->with('duplicate_warning', $result['message']);
        }

        // Gate 2: Nominal soft warning — show confirmation popup
        if ($result['needs_nominal_confirmation'] ?? false) {
            return redirect()->route('tickets.show', $ticket)
                ->with('needs_nominal_confirmation', true)
                ->with('nominal_warning', $result['message']);
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
     * Team Leader: Bulk review documents — accept or reject multiple tickets at once.
     *
     * Action 'accept': All docs set to accepted → ticket status: need_to_validate
     * Action 'reject': All docs set to rejected → ticket status: revision
     * Notes: One shared note applied to all selected tickets.
     */
    public function bulkReview(Request $request): RedirectResponse
    {
        $request->validate([
            'action'       => ['required', 'in:accept,reject'],
            'ticket_ids'   => ['required', 'array', 'min:1'],
            'ticket_ids.*' => ['integer', 'exists:tickets,id'],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ]);

        $user    = $request->user();
        $tickets = Ticket::with('documents')
            ->whereIn('id', $request->ticket_ids)
            ->where('status', Ticket::STATUS_PENDING_REVIEW)
            ->get();

        if ($tickets->isEmpty()) {
            return back()->with('error', 'Tidak ada tiket valid untuk diproses. Pastikan tiket berstatus Menunggu Cek Dokumen.');
        }

        $isAccept = $request->action === 'accept';
        $count    = $tickets->count();
        $notes    = $request->notes ?? ($isAccept ? 'Semua dokumen diterima.' : 'Dokumen ditolak.');

        DB::transaction(function () use ($tickets, $user, $isAccept, $notes) {
            foreach ($tickets as $ticket) {
                // Update all documents on this ticket
                foreach ($ticket->documents as $doc) {
                    $doc->update([
                        'status'   => $isAccept ? 'accepted' : 'rejected',
                        'feedback' => $isAccept ? null : $notes,
                    ]);
                }

                // Update ticket status
                $ticket->update([
                    'status' => $isAccept
                        ? Ticket::STATUS_NEED_TO_VALIDATE
                        : Ticket::STATUS_REVISION,
                ]);

                // Log per ticket
                ApprovalLog::create([
                    'ticket_id' => $ticket->id,
                    'user_id'   => $user->id,
                    'action'    => $isAccept ? ApprovalLog::ACTION_FOLLOWED_UP : ApprovalLog::ACTION_REJECTED_DOCUMENT,
                    'notes'     => $notes,
                ]);

                // Notify each requester
                if ($isAccept) {
                    Notification::notify(
                        $ticket->user_id,
                        'ticket_reviewed',
                        'Dokumen Diterima',
                        "Dokumen tiket \"{$ticket->title}\" diterima. Silakan jalankan Smart Validation.",
                        $ticket->id
                    );
                } else {
                    Notification::notify(
                        $ticket->user_id,
                        'ticket_revised',
                        'Dokumen Perlu Revisi',
                        "Dokumen tiket \"{$ticket->title}\" ditolak. Catatan: {$notes}",
                        $ticket->id
                    );
                }
            }
        });

        $verb      = $isAccept ? 'diterima' : 'ditolak';
        $newStatus = $isAccept ? 'need_to_validate' : 'revision';

        return redirect()->route('tickets.index', ['status' => $newStatus])
            ->with('success', "{$count} tiket berhasil diproses — dokumen {$verb}.");
    }

    /**
     * Department Head: Final approve or decline decision.
     * Budget lock (if normal flow, no cross-fund) also happens HERE.
     */
    public function decide(Request $request, Ticket $ticket): RedirectResponse
    {
        $request->validate([
            'action' => ['required', 'in:approve,decline'],
            'notes'  => ['nullable', 'string', 'max:1000'],
        ]);

        $this->ensureStatus($ticket, Ticket::STATUS_PENDING_DEPT_HEAD);

        $user = $request->user();

        $message = DB::transaction(function () use ($request, $ticket, $user) {
            if ($request->action === 'approve') {
                // Permanently deduct budget on approval
                // For cross-fund: budget was locked in applyCrossFund → permanentDeduct converts lock to permanent
                // For normal flow: budget was NOT locked (Revisi 3), so permanentDeduct without prior lock
                $budget = Budget::findForTicket(
                    $ticket->expenditure_type,
                    $ticket->category,
                    now()->year
                );

                if ($budget) {
                    $budget->permanentDeduct($ticket->total_amount);
                }

                $ticket->update(['status' => Ticket::STATUS_APPROVED]);

                ApprovalLog::create([
                    'ticket_id' => $ticket->id,
                    'user_id'   => $user->id,
                    'action'    => ApprovalLog::ACTION_APPROVED,
                    'notes'     => $request->notes,
                ]);

                // Notify Requester and Team Leaders (TL generates the form)
                Notification::notify(
                    $ticket->user_id,
                    'ticket_approved',
                    'Pengajuan Disetujui!',
                    "Tiket \"{$ticket->title}\" telah disetujui oleh Department Head.",
                    $ticket->id
                );
                Notification::notifyRole(
                    'team_leader',
                    'ticket_approved',
                    'Siap Generate Form',
                    "Tiket \"{$ticket->title}\" disetujui. Silakan terbitkan Form Pengadaan.",
                    $ticket->id
                );

                return 'Pengadaan disetujui. Team Leader dapat menerbitkan Form Pengadaan.';
            } else {
                // Decline: only release lock if this was a cross-fund ticket
                // Normal flow tickets have no lock to release (lock removed at Gate 4 per Revisi 3)
                if ($ticket->is_cross_fund) {
                    $budget = Budget::findForTicket(
                        $ticket->expenditure_type,
                        $ticket->category,
                        now()->year
                    );
                    if ($budget) {
                        $budget->unlock($ticket->total_amount);
                    }
                }

                $ticket->update(['status' => Ticket::STATUS_DECLINED]);

                ApprovalLog::create([
                    'ticket_id' => $ticket->id,
                    'user_id'   => $user->id,
                    'action'    => ApprovalLog::ACTION_DECLINED,
                    'notes'     => $request->notes,
                ]);

                // Notify Requester
                Notification::notify(
                    $ticket->user_id,
                    'ticket_declined',
                    'Pengajuan Ditolak',
                    "Tiket \"{$ticket->title}\" ditolak oleh Department Head.",
                    $ticket->id
                );

                return 'Pengadaan ditolak. Tiket tidak dapat dilanjutkan.';
            }
        });

        return redirect()->route('tickets.show', $ticket)
            ->with('success', $message);
    }

    /**
     * Department Head: Bulk approve or decline multiple tickets.
     */
    public function bulkDecide(Request $request): RedirectResponse
    {
        $request->validate([
            'action'       => ['required', 'in:approve,decline'],
            'ticket_ids'   => ['required', 'array', 'min:1'],
            'ticket_ids.*' => ['integer', 'exists:tickets,id'],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ]);

        $user    = $request->user();
        $tickets = Ticket::whereIn('id', $request->ticket_ids)
            ->where('status', Ticket::STATUS_PENDING_DEPT_HEAD)
            ->get();

        if ($tickets->isEmpty()) {
            return back()->with('error', 'Tidak ada tiket valid untuk diproses.');
        }

        $count = $tickets->count();

        DB::transaction(function () use ($tickets, $user, $request) {
            foreach ($tickets as $ticket) {
                if ($request->action === 'approve') {
                    $budget = Budget::findForTicket(
                        $ticket->expenditure_type,
                        $ticket->category,
                        now()->year
                    );
                    if ($budget) {
                        $budget->permanentDeduct($ticket->total_amount);
                    }
                    $ticket->update(['status' => Ticket::STATUS_APPROVED]);
                    ApprovalLog::create([
                        'ticket_id' => $ticket->id,
                        'user_id'   => $user->id,
                        'action'    => ApprovalLog::ACTION_APPROVED,
                        'notes'     => $request->notes,
                    ]);
                } else {
                    if ($ticket->is_cross_fund) {
                        $budget = Budget::findForTicket(
                            $ticket->expenditure_type,
                            $ticket->category,
                            now()->year
                        );
                        if ($budget) {
                            $budget->unlock($ticket->total_amount);
                        }
                    }
                    $ticket->update(['status' => Ticket::STATUS_DECLINED]);
                    ApprovalLog::create([
                        'ticket_id' => $ticket->id,
                        'user_id'   => $user->id,
                        'action'    => ApprovalLog::ACTION_DECLINED,
                        'notes'     => $request->notes,
                    ]);
                }
            }
        });

        $verb = $request->action === 'approve' ? 'disetujui' : 'ditolak';
        return redirect()->route('tickets.index', ['status' => $request->action === 'approve' ? 'approved' : 'declined'])
            ->with('success', "{$count} tiket berhasil {$verb}.");
    }

    public function streamDocument(TicketDocument $ticketDocument, Request $request)
    {
        $this->authorizeView($ticketDocument->ticket, $request->user());

        if (!$ticketDocument->file_path || !Storage::disk('public')->exists($ticketDocument->file_path)) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        $path = Storage::disk('public')->path($ticketDocument->file_path);

        if ($request->query('download')) {
            return response()->download($path, str_replace(' ', '_', $ticketDocument->description) . '.pdf');
        }

        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($ticketDocument->file_path) . '"'
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
                'pending_review'   => 'Menunggu Cek Dokumen (Team Leader)',
                'need_to_validate' => 'Menunggu Smart Validation',
                'pending_dept_head'=> 'Menunggu Department Head',
                'approved'         => 'Disetujui',
                'declined'         => 'Ditolak',
                'revision'         => 'Perlu Revisi Dokumen',
                'form_generated'   => 'Form Diterbitkan',
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
