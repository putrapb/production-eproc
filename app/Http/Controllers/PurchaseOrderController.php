<?php

namespace App\Http\Controllers;

use App\Models\ApprovalLog;
use App\Models\Notification;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class PurchaseOrderController extends Controller
{
    /**
     * Team Leader: Generate Form Pengadaan PDF and save to local public storage.
     * Only available when ticket status is 'approved'.
     */
    public function generate(Request $request, Ticket $ticket): RedirectResponse
    {
        $request->validate([
            'digital_signature_consent' => 'required|accepted',
        ], [
            'digital_signature_consent.required' => 'Anda harus menyetujui syarat & ketentuan digital signature.',
            'digital_signature_consent.accepted' => 'Anda harus menyetujui syarat & ketentuan digital signature.',
        ]);

        if (! $ticket->isApproved()) {
            return redirect()->route('tickets.show', $ticket)
                ->with('error', 'Form Pengadaan hanya dapat diterbitkan untuk tiket yang berstatus Disetujui. Status saat ini: ' . $ticket->status_label . '.');
        }

        $ticket->load(['user.hrEmployee', 'approvalLogs.user.hrEmployee']);

        // Eager-load the generating user's HR data for Form template
        $generatedBy = $request->user()->load('hrEmployee');

        // Generate PDF content — uses Blade view for Form Pengadaan layout
        $pdf = Pdf::loadView('pdf.purchase-order', [
            'ticket'       => $ticket,
            'generated_at' => now(),
            'generated_by' => $generatedBy,
        ])->setPaper('a4', 'portrait');

        $pdfContent = $pdf->output();

        // Save to local public storage
        $folder   = config('eprocurement.storage.purchase_orders_folder', 'purchase_orders');
        $filename = "FORM-{$ticket->id}-" . \Illuminate\Support\Str::random(16) . '.pdf';
        $path     = $folder . '/' . $filename;

        Storage::disk('public')->put($path, $pdfContent);

        // Update ticket
        $ticket->update([
            'document_po_path' => $path,
            'status'           => Ticket::STATUS_FORM_GENERATED,
        ]);

        ApprovalLog::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $request->user()->id,
            'action'    => 'form_issued',
            'notes'     => "Form Pengadaan diterbitkan: {$filename}",
        ]);

        // Notify Requester that Form is ready
        Notification::notify(
            $ticket->user_id,
            'form_generated',
            'Form Pengadaan Siap Diunduh',
            "Form Pengadaan untuk tiket \"{$ticket->title}\" telah diterbitkan oleh Team Leader.",
            $ticket->id
        );

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Form Pengadaan berhasil diterbitkan. Klik "Unduh Form" untuk mengunduh dokumen.');
    }

    /**
     * Requester & Team Leader: Download Form PDF as attachment.
     */
    public function download(Request $request, Ticket $ticket): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\Response|RedirectResponse
    {
        if ($request->user()->isRequester()) {
            abort_if($ticket->user_id !== auth()->id(), 403);
        }

        if (! $ticket->isFormGenerated()) {
            return redirect()->route('tickets.show', $ticket)
                ->with('error', 'Form Pengadaan belum tersedia untuk tiket ini.');
        }

        if (! $ticket->document_po_path) {
            return redirect()->route('tickets.show', $ticket)
                ->with('error', 'File Form Pengadaan tidak ditemukan di storage.');
        }

        if (! Storage::disk('public')->exists($ticket->document_po_path)) {
            return redirect()->route('tickets.show', $ticket)
                ->with('error', 'File Form Pengadaan tidak dapat diakses. Hubungi administrator.');
        }

        $path     = Storage::disk('public')->path($ticket->document_po_path);
        $filename = 'FORM-' . str_pad($ticket->id, 6, '0', STR_PAD_LEFT) . '.pdf';

        if ($request->query('download')) {
            return response()->download($path, $filename);
        }

        $headers = [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control'       => 'no-cache, must-revalidate',
        ];

        return response()->file($path, $headers);
    }
}
