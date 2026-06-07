<?php

namespace App\Http\Controllers;

use App\Models\ApprovalLog;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class PurchaseOrderController extends Controller
{
    /**
     * PFA: Generate Purchase Order PDF and save to local public storage.
     * Only available when ticket status is 'approved'.
     */
    public function generate(Request $request, Ticket $ticket): RedirectResponse
    {
        if (! $ticket->isApproved()) {
            return redirect()->route('tickets.show', $ticket)
                ->with('error', 'Purchase Order hanya dapat diterbitkan untuk tiket yang berstatus Disetujui. Status saat ini: ' . $ticket->status_label . '.');
        }

        $ticket->load(['user.hrEmployee', 'approvalLogs.user.hrEmployee']);

        // Eager-load the generating user's HR data for PO template
        $generatedBy = $request->user()->load('hrEmployee');

        // Generate PDF content — uses Blade view for PO document layout
        $pdf = Pdf::loadView('pdf.purchase-order', [
            'ticket'       => $ticket,
            'generated_at' => now(),
            'generated_by' => $generatedBy,
        ])->setPaper('a4', 'portrait');

        $pdfContent = $pdf->output();

        // Save to local public storage
        $folder   = config('eprocurement.storage.purchase_orders_folder', 'purchase_orders');
        $filename = "PO-{$ticket->id}-" . \Illuminate\Support\Str::random(16) . '.pdf';
        $path     = $folder . '/' . $filename;

        Storage::disk('public')->put($path, $pdfContent);

        // Update ticket
        $ticket->update([
            'document_po_path' => $path,
            'status'           => Ticket::STATUS_PO_GENERATED,
        ]);

        ApprovalLog::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $request->user()->id,
            'action'    => ApprovalLog::ACTION_PO_ISSUED,
            'notes'     => "Purchase Order diterbitkan: {$filename}",
        ]);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Purchase Order berhasil diterbitkan. Klik "Unduh PO" untuk mengunduh dokumen.');
    }

    /**
     * Requester & PFA: Download PO PDF as attachment.
     * Route is protected by role:requester,pfa middleware.
     */
    public function download(Request $request, Ticket $ticket): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\Response|RedirectResponse
    {
        if ($request->user()->isRequester()) {
            abort_if($ticket->user_id !== auth()->id(), 403);
        }

        if (! $ticket->isPoGenerated()) {
            return redirect()->route('tickets.show', $ticket)
                ->with('error', 'Purchase Order belum tersedia untuk tiket ini.');
        }

        if (! $ticket->document_po_path) {
            return redirect()->route('tickets.show', $ticket)
                ->with('error', 'File Purchase Order tidak ditemukan di storage.');
        }

        if (! Storage::disk('public')->exists($ticket->document_po_path)) {
            return redirect()->route('tickets.show', $ticket)
                ->with('error', 'File Purchase Order tidak dapat diakses. Hubungi administrator.');
        }

        $path = Storage::disk('public')->path($ticket->document_po_path);
        $filename = 'PO-' . str_pad($ticket->id, 6, '0', STR_PAD_LEFT) . '.pdf';

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
