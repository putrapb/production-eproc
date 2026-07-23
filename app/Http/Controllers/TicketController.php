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
     * Daftar tiket, difilter berdasarkan role user yang login.
     */
    public function index(Request $request): View
    {
        $user    = $request->user();
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100])
            ? (int) $request->per_page
            : 15;

        $tickets = Ticket::with(['user', 'approvalLogs.user'])
            ->when($request->user()->isRequester(), fn ($q) => $q->where('user_id', $request->user()->id))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->pending_with, function ($q, $p) {
                $q->where(function ($q) use ($p) {
                    $q->where('pending_with_role', $p)
                      ->orWhere(function ($q) use ($p) {
                          $q->whereNull('pending_with_role');
                          if ($p === 'team_leader') {
                              $q->whereIn('status', [Ticket::STATUS_PENDING_REVIEW, Ticket::STATUS_NEED_TO_VALIDATE, Ticket::STATUS_APPROVED]);
                          } elseif ($p === 'department_head') {
                              $q->where('status', Ticket::STATUS_PENDING_DEPT_HEAD);
                          } elseif ($p === 'requester') {
                              $q->where('status', Ticket::STATUS_REVISION);
                          } else {
                              $q->whereRaw('1 = 0');
                          }
                      });
                });
            })
            ->when($request->search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('vendor_name', 'like', "%{$s}%");
            }))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('tickets.index', compact('tickets', 'user', 'perPage'));
    }

    /**
     * Export tickets as CSV.
     */
    public function export(Request $request)
    {
        $tickets = Ticket::with(['user'])
            ->when($request->user()->isRequester(), fn ($q) => $q->where('user_id', $request->user()->id))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->pending_with, function ($q, $p) {
                $q->where(function ($q) use ($p) {
                    $q->where('pending_with_role', $p)
                      ->orWhere(function ($q) use ($p) {
                          $q->whereNull('pending_with_role');
                          if ($p === 'team_leader') {
                              $q->whereIn('status', [Ticket::STATUS_PENDING_REVIEW, Ticket::STATUS_NEED_TO_VALIDATE, Ticket::STATUS_APPROVED]);
                          } elseif ($p === 'department_head') {
                              $q->where('status', Ticket::STATUS_PENDING_DEPT_HEAD);
                          } elseif ($p === 'requester') {
                              $q->where('status', Ticket::STATUS_REVISION);
                          } else {
                              $q->whereRaw('1 = 0');
                          }
                      });
                });
            })
            ->when($request->search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('vendor_name', 'like', "%{$s}%");
            }))
            ->latest()
            ->get();

        $filename = 'export_tiket_pengadaan_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($tickets) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 compatibility
            fputs($file, "\xEF\xBB\xBF");
            
            fputcsv($file, [
                'ID Tiket', 
                'Tanggal Dibuat', 
                'Judul', 
                'Nama Barang', 
                'Vendor', 
                'Kategori', 
                'Jenis Pengeluaran', 
                'Silang Dana', 
                'Total Nominal (Rp)', 
                'Pemohon', 
                'Status', 
                'Bola Di'
            ]);

            foreach ($tickets as $ticket) {
                fputcsv($file, [
                    $ticket->id,
                    $ticket->created_at->format('Y-m-d H:i:s'),
                    $ticket->title,
                    $ticket->item_name ?? '-',
                    $ticket->vendor_name,
                    config('eprocurement.categories.' . $ticket->category, $ticket->category),
                    $ticket->expenditure_type ?? '-',
                    $ticket->is_cross_fund ? 'Ya' : 'Tidak',
                    $ticket->total_amount,
                    $ticket->user?->name ?? 'Unknown',
                    $ticket->status_label,
                    $ticket->ball_holder ?? '-'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Tampilkan detail tiket beserta riwayat persetujuannya.
     */
    public function show(Ticket $ticket, Request $request): View
    {
        $user = $request->user();

        // Pastikan user punya akses ke tiket ini
        $this->authorizeView($ticket, $user);

        $ticket->load(['user', 'approvalLogs.user', 'documents']);

        return view('tickets.show', compact('ticket', 'user'));
    }

    /**
     * Tampilkan form buat tiket baru. (Requester only)
     */
    public function create(): View
    {
        return view('tickets.create');
    }

    /**
     * Transkrip 2 — Upfront SmartVal Preview (AJAX).
     * Requester menjalankan ini dari form create/edit SEBELUM submit.
     * Tidak menyimpan apapun ke DB — hanya memberikan saran klasifikasi & budget.
     */
    public function previewValidation(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'title'              => ['nullable', 'string', 'max:255'],
            'category'           => ['nullable', 'in:infrastruktur_utama,lisensi_sistem,layanan_pemeliharaan,perlengkapan_operasional'],
            'expenditure_type'   => ['nullable', 'in:CAPEX,OPEX'],
            'items'              => ['nullable', 'array', 'max:9'],
            'items.*.item_name'  => ['nullable', 'string', 'max:255'],
            'items.*.quantity'   => ['nullable', 'integer', 'min:1', 'max:10000'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
        ]);

        $result = $this->smartValidation->preview(
            $request->only(['title', 'category', 'expenditure_type', 'items']),
            $request->user()
        );

        return response()->json($result);
    }

    /**
     * Store a new ticket. (Requester only)
     */
    public function store(StoreTicketRequest $request): RedirectResponse
    {
        $user = $request->user();

        // 1. Calculate total amount
        $totalAmount = collect($request->items)->sum(function ($item) {
            return ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0);
        });

        // 2. Create the ticket
        $ticket = Ticket::create([
            'user_id'            => $user->id,
            'title'              => $request->title,
            'category'           => $request->category,
            'description'        => $request->description,
            'pic_name'           => empty(array_filter((array) $request->pic_name, fn($n) => !empty(trim($n))))
                                    ? null
                                    : array_values(array_filter((array) $request->pic_name, fn($n) => !empty(trim($n)))),
            'vendor_name'        => $request->vendor_name,
            'amount'             => $totalAmount,
            'status'             => Ticket::STATUS_PENDING_REVIEW,
            'pending_with_role'  => 'team_leader', // Transkrip 2: track pending holder
        ]);

        // 3. Save ticket items
        if ($request->has('items') && is_array($request->items)) {
            foreach ($request->items as $itemData) {
                $qty = $itemData['quantity'] ?? 1;
                $price = $itemData['unit_price'] ?? 0;
                $ticket->items()->create([
                    'item_name'        => $itemData['item_name'],
                    'quantity'         => $qty,
                    'unit_price'       => $price,
                    'subtotal'         => $qty * $price,
                    'expenditure_type' => $itemData['expenditure_type'] ?? null,
                ]);
            }
        }

        // 4. Upload and save each document
        $folder = config('eprocurement.storage.izin_prinsip_folder', 'izin_prinsip');
        $firstPath = null;

        if ($request->hasFile('document_files')) {
            foreach ($request->file('document_files') as $index => $file) {
                $description = $request->document_descriptions[$index] ?? 'Dokumen Pendukung';
                $path = $file->store($folder, 'public');

                if ($index === 0) $firstPath = $path;

                TicketDocument::create([
                    'ticket_id'   => $ticket->id,
                    'file_path'   => $path,
                    'description' => $description,
                    'status'      => 'pending',
                ]);
            }
        }

        if ($firstPath) {
            $ticket->update(['document_path' => $firstPath]);
        }

        // 5. Log the submission
        ApprovalLog::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'action'    => ApprovalLog::ACTION_SUBMITTED,
        ]);

        // 6. Notify Team Leaders (targeted: notifyRole karena TL belum di-assign spesifik)
        Notification::notifyRole(
            'team_leader',
            'ticket_submitted',
            'Pengajuan Baru Masuk',
            "{$user->name} mengajukan tiket: \"{$ticket->title}\". Silakan cek dokumen.",
            $ticket->id
        );
        // Requester tidak dapat notif saat ini — bola di tangan TL

        return redirect()->route('tickets.index')
            ->with('success', 'Tiket pengadaan berhasil diajukan.');
    }

    /**
     * Tampilkan form edit tiket. (Requester, status: revision atau pending_review)
     */
    public function edit(Ticket $ticket, Request $request): View|RedirectResponse
    {
        abort_if($ticket->user_id !== auth()->id(), 403, 'Akses Ditolak: Anda tidak berhak memodifikasi tiket ini.');

        abort_unless(in_array($ticket->status, [Ticket::STATUS_REVISION, Ticket::STATUS_PENDING_REVIEW]), 403, 'Tiket tidak dapat diedit pada status ini.');

        $ticket->load(['approvalLogs', 'documents']);

        return view('tickets.edit', compact('ticket'));
    }

    /**
     * Simpan perubahan tiket yang diedit. (Requester, status: revision atau pending_review)
     */
    public function update(\App\Http\Requests\UpdateTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        abort_if($ticket->user_id !== auth()->id(), 403, 'Akses Ditolak: Anda tidak berhak memodifikasi tiket ini.');

        $isRevision = $ticket->status === Ticket::STATUS_REVISION;

        // 1. Calculate new total amount
        $totalAmount = collect($request->items)->sum(function ($item) {
            return ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0);
        });

        // 2. Update Ticket attributes
        $ticket->update([
            'title'            => $request->title,
            'category'         => $request->category,
            'description'      => $request->description,
            'pic_name'         => empty(array_filter((array) $request->pic_name, fn($n) => !empty(trim($n))))
                                    ? null
                                    : array_values(array_filter((array) $request->pic_name, fn($n) => !empty(trim($n)))),
            'vendor_name'      => $request->vendor_name,
            'amount'           => $totalAmount,
        ]);

        // 3. Update ticket items
        $ticket->items()->delete();
        if ($request->has('items') && is_array($request->items)) {
            foreach ($request->items as $itemData) {
                $qty = $itemData['quantity'] ?? 1;
                $price = $itemData['unit_price'] ?? 0;
                $ticket->items()->create([
                    'item_name'        => $itemData['item_name'],
                    'quantity'         => $qty,
                    'unit_price'       => $price,
                    'subtotal'         => $qty * $price,
                    'expenditure_type' => $itemData['expenditure_type'] ?? null,
                ]);
            }
        }

        $folder = config('eprocurement.storage.izin_prinsip_folder', 'izin_prinsip');

        // 4. Process replacements for existing rejected documents
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
            'action'    => $isRevision ? ApprovalLog::ACTION_REVISED : ApprovalLog::ACTION_EDITED,
            'notes'     => $isRevision ? 'Tiket direvisi oleh Requester.' : 'Tiket diedit oleh Requester.',
        ]);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Tiket berhasil direvisi dan dikembalikan ke status Pending Review.');
    }

    /**
     * Team Leader: review dokumen pendukung — terima atau tolak per dokumen.
     */
    public function review(Request $request, Ticket $ticket): RedirectResponse
    {
        $request->validate([
            'document_status'             => ['required', 'array'],
            'document_status.*'           => ['required', 'in:accepted,rejected'],
            'document_feedback'           => ['nullable', 'array'],
            'document_feedback.*'         => ['nullable', 'string', 'max:1000'],
            'notes'                       => ['nullable', 'string', 'max:1000'],
            'digital_signature_consent'   => ['required', 'accepted'],
        ], [
            'digital_signature_consent.required' => 'Anda harus menyetujui syarat & ketentuan digital signature.',
            'digital_signature_consent.accepted' => 'Anda harus menyetujui syarat & ketentuan digital signature.',
        ]);

        $this->ensureStatus($ticket, Ticket::STATUS_PENDING_REVIEW);

        $hasRejected = false;
        $rejectedNames = [];
        $result = [];

        DB::transaction(function () use ($request, $ticket, &$hasRejected, &$rejectedNames, &$result) {
            foreach ($ticket->documents as $doc) {
                $status   = $request->document_status[$doc->id] ?? 'accepted';
                $feedback = $request->document_feedback[$doc->id] ?? null;

                if ($status === 'rejected') {
                    $hasRejected    = true;
                    $rejectedNames[] = $doc->description;
                }

                $doc->update([
                    'status'   => $status,
                    'feedback' => $status === 'rejected' ? $feedback : null,
                ]);
            }

            if ($hasRejected) {
                $ticket->update(['status' => Ticket::STATUS_REVISION]);
                return;
            }

            $ticket->update([
                'pending_with_role' => 'requester',
                'status'            => Ticket::STATUS_NEED_TO_VALIDATE,
            ]);

            ApprovalLog::create([
                'ticket_id' => $ticket->id,
                'user_id'   => $request->user()->id,
                'action'    => ApprovalLog::ACTION_FOLLOWED_UP,
                'notes'     => $request->notes ?? 'Semua dokumen disetujui. Requester diminta menjalankan Smart Validation.',
            ]);
        });

        if ($hasRejected) {
            ApprovalLog::create([
                'ticket_id' => $ticket->id,
                'user_id'   => $request->user()->id,
                'action'    => ApprovalLog::ACTION_REJECTED_DOCUMENT,
                'notes'     => $request->notes ?? 'Beberapa dokumen ditolak.',
            ]);

            Notification::notify(
                $ticket->user_id,
                'ticket_revised',
                'Dokumen Perlu Revisi',
                'Dokumen berikut perlu direvisi: ' . implode(', ', $rejectedNames) . '. Catatan: ' . ($request->notes ?? 'Silakan periksa detail tiket.'),
                $ticket->id
            );

            return redirect()->route('tickets.show', $ticket)
                ->with('success', 'Beberapa dokumen memerlukan revisi. Tiket dikembalikan ke Requester.');
        }

        // Semua dokumen diterima → notif ke Requester untuk jalankan Smart Validation
        Notification::notify(
            $ticket->user_id,
            'ticket_reviewed',
            'Dokumen Diterima — Jalankan Smart Validation',
            "Dokumen tiket \"{$ticket->title}\" telah diterima oleh Team Leader. Silakan buka tiket dan jalankan Smart Validation.",
            $ticket->id
        );

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Semua dokumen diterima. Requester dapat menjalankan Smart Validation sekarang.');
    }

    /**
     * Requester: Run Smart Validation (4-Gate Engine) after PFA accepts the document.
     * Supports soft-warning confirmations for Gate 1 (duplicate) and Gate 2 (nominal).
     */
    public function runSmartValidation(Request $request, Ticket $ticket): RedirectResponse
    {
        if (!$request->has('duplicate_confirmed') && !$request->has('nominal_confirmed')) {
            $request->validate([
                'digital_signature_consent' => 'required|accepted',
            ], [
                'digital_signature_consent.required' => 'Anda harus menyetujui syarat & ketentuan digital signature.',
                'digital_signature_consent.accepted' => 'Anda harus menyetujui syarat & ketentuan digital signature.',
            ]);
        }
        if (auth()->user()->isRequester()) {
            abort_if($ticket->user_id !== auth()->id(), 403);
        }

        $this->ensureStatus($ticket, Ticket::STATUS_NEED_TO_VALIDATE);

        $user   = $request->user();
        $result = $this->smartValidation->run(
            $ticket,
            $user,
            duplicateConfirmed:      (bool) $request->boolean('duplicate_confirmed'),
            nominalConfirmed:        (bool) $request->boolean('nominal_confirmed'),
            classificationConfirmed: (bool) $request->boolean('classification_confirmed'),
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

        // Gate 3: Classification mismatch soft warning
        if ($result['needs_classification_confirmation'] ?? false) {
            return redirect()->route('tickets.show', $ticket)
                ->with('needs_classification_confirmation', true)
                ->with('classification_warning', $result['message']);
        }

        if ($result['over_budget']) {
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
        abort_if($ticket->user_id !== auth()->id(), 403, 'Akses Ditolak: Anda tidak berhak membatalkan tiket ini.');

        $this->ensureStatus($ticket, Ticket::STATUS_NEED_TO_VALIDATE);

        \Illuminate\Support\Facades\DB::transaction(function () use ($ticket, $request) {
            $total = (float) $ticket->amount;
            $type  = $ticket->expenditure_type;

            if ($type === Ticket::TYPE_CAPEX && $total > 0) {
                $budget = Budget::findForTicket(Ticket::TYPE_CAPEX, $ticket->category, now()->year);
                if ($budget) $budget->unlock($total);
            } elseif ($total > 0) {
                $budget = Budget::findForTicket(Ticket::TYPE_OPEX, $ticket->category, now()->year);
                if ($budget) $budget->unlock($total);
            }

            $ticket->update(['status' => Ticket::STATUS_DECLINED]);

            ApprovalLog::create([
                'ticket_id' => $ticket->id,
                'user_id'   => $request->user()->id,
                'action'    => 'declined',
                'notes'     => $request->notes ?? 'Dibatalkan oleh Requester karena tidak mengajukan silang dana / hasil peninjauan internal.',
            ]);
        });

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
                        ? Ticket::STATUS_PENDING_DEPT_HEAD  // Smart Val runs inline via review() for individual tickets
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
                        "Dokumen tiket \"{$ticket->title}\" diterima. Tiket telah diteruskan ke Department Head.",
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
        $rules = [
            'action' => ['required', 'in:approve,decline'],
            'notes'  => ['nullable', 'string', 'max:1000'],
        ];

        if ($request->action === 'approve') {
            $rules['digital_signature_consent'] = ['required', 'accepted'];
        }

        $request->validate($rules, [
            'digital_signature_consent.required' => 'Anda harus menyetujui syarat & ketentuan digital signature.',
            'digital_signature_consent.accepted' => 'Anda harus menyetujui syarat & ketentuan digital signature.',
        ]);

        $this->ensureStatus($ticket, Ticket::STATUS_PENDING_DEPT_HEAD);

        $user = $request->user();

        $message = DB::transaction(function () use ($request, $ticket, $user) {
            $capexTotal = $ticket->expenditure_type === Ticket::TYPE_CAPEX ? (float) $ticket->amount : 0;
            $opexTotal  = $ticket->expenditure_type === Ticket::TYPE_OPEX  ? (float) $ticket->amount : 0;

            if ($request->action === 'approve') {
                if ($capexTotal > 0) {
                    $budget = Budget::findForTicket(Ticket::TYPE_CAPEX, $ticket->category, now()->year);
                    if ($budget) $budget->permanentDeduct($capexTotal);
                }
                if ($opexTotal > 0) {
                    $budget = Budget::findForTicket(Ticket::TYPE_OPEX, $ticket->category, now()->year);
                    if ($budget) $budget->permanentDeduct($opexTotal);
                }

                $ticket->update(['status' => Ticket::STATUS_APPROVED]);

                ApprovalLog::create([
                    'ticket_id' => $ticket->id,
                    'user_id'   => $user->id,
                    'action'    => ApprovalLog::ACTION_APPROVED,
                    'notes'     => $request->notes,
                ]);

                // Notify Requester dan TL yang membuat form (bola ke TL)
                Notification::notify(
                    $ticket->user_id,
                    'ticket_approved',
                    'Pengajuan Disetujui!',
                    "Tiket \"{$ticket->title}\" telah disetujui oleh Department Head.",
                    $ticket->id
                );
                // Targeted ke TL: notifyRole masih dipakai karena TL belum di-assign spesifik
                Notification::notifyRole(
                    'team_leader',
                    'ticket_approved',
                    'Siap Generate Form',
                    "Tiket \"{$ticket->title}\" disetujui. Silakan terbitkan Form Pengadaan.",
                    $ticket->id
                );
                // Bola ke TL untuk generate form
                $ticket->update(['pending_with_role' => 'team_leader']);

                return 'Pengadaan disetujui. Team Leader dapat menerbitkan Form Pengadaan.';
            } else {
                if ($capexTotal > 0) {
                    $budget = Budget::findForTicket(Ticket::TYPE_CAPEX, $ticket->category, now()->year);
                    if ($budget) $budget->unlock($capexTotal);
                }
                if ($opexTotal > 0) {
                    $budget = Budget::findForTicket(Ticket::TYPE_OPEX, $ticket->category, now()->year);
                    if ($budget) $budget->unlock($opexTotal);
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
                $capexTotal = $ticket->expenditure_type === Ticket::TYPE_CAPEX ? (float) $ticket->amount : 0;
                $opexTotal  = $ticket->expenditure_type === Ticket::TYPE_OPEX  ? (float) $ticket->amount : 0;

                if ($request->action === 'approve') {
                    if ($capexTotal > 0) {
                        $budget = Budget::findForTicket(Ticket::TYPE_CAPEX, $ticket->category, now()->year);
                        if ($budget) $budget->permanentDeduct($capexTotal);
                    }
                    if ($opexTotal > 0) {
                        $budget = Budget::findForTicket(Ticket::TYPE_OPEX, $ticket->category, now()->year);
                        if ($budget) $budget->permanentDeduct($opexTotal);
                    }
                    $ticket->update(['status' => Ticket::STATUS_APPROVED]);
                    ApprovalLog::create([
                        'ticket_id' => $ticket->id,
                        'user_id'   => $user->id,
                        'action'    => ApprovalLog::ACTION_APPROVED,
                        'notes'     => $request->notes,
                    ]);
                } else {
                    if ($capexTotal > 0) {
                        $budget = Budget::findForTicket(Ticket::TYPE_CAPEX, $ticket->category, now()->year);
                        if ($budget) $budget->unlock($capexTotal);
                    }
                    if ($opexTotal > 0) {
                        $budget = Budget::findForTicket(Ticket::TYPE_OPEX, $ticket->category, now()->year);
                        if ($budget) $budget->unlock($opexTotal);
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

    // Verifikasi publik (link QR code di dokumen)

    public function verifyPublic(Request $request, Ticket $ticket)
    {
        if (!$request->hasValidSignature()) {
            abort(401, 'Tautan verifikasi tidak valid atau telah kadaluarsa.');
        }

        if (!$ticket->po_path || !Storage::disk('public')->exists($ticket->po_path)) {
            abort(404, 'Dokumen Form Pengadaan belum tersedia.');
        }

        $path = Storage::disk('public')->path($ticket->po_path);

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="FORM-' . str_pad($ticket->id, 6, '0', STR_PAD_LEFT) . '.pdf"'
        ]);
    }

    // Helper privat

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
        // M-2: INTENTIONAL — Semua role authenticated dapat melihat semua tiket.
        // Desain ini disengaja untuk mendukung tracking & transparansi lintas role.
        // Pembatasan aksi (edit, approve, cancel, dll) dilakukan di:
        //   1. Route middleware: role:requester, role:team_leader, dll
        //   2. Method-level: abort_if($ticket->user_id !== auth()->id(), 403)
        //   3. ensureStatus(): memastikan aksi hanya valid pada status yang benar
        // Jangan hapus method ini — dipakai oleh streamDocument() sebagai hook.
    }
}
