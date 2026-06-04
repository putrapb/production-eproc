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
     * PFA: Generate Purchase Order PDF and upload to Supabase Storage.
     * Only available when ticket status is 'approved'.
     */
    public function generate(Request $request, Ticket $ticket): RedirectResponse
    {
        if (! $ticket->isApproved()) {
            abort(422, 'Purchase Order hanya dapat diterbitkan untuk tiket yang sudah disetujui.');
        }

        $ticket->load(['user.hrEmployee', 'approvalLogs.user']);

        // Generate PDF content — uses Blade view for PO document layout
        $pdf = Pdf::loadView('pdf.purchase-order', [
            'ticket' => $ticket,
            'generated_at' => now(),
            'generated_by' => $request->user(),
        ])->setPaper('a4', 'portrait');

        $pdfContent = $pdf->output();

        // Upload to Supabase Storage
        $folder   = config('eprocurement.storage.purchase_orders_folder', 'purchase_orders');
        $filename = "PO-{$ticket->id}-" . now()->format('YmdHis') . '.pdf';
        $path     = $folder . '/' . $filename;

        Storage::disk('s3')->put($path, $pdfContent, 'private');

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
            ->with('success', 'Purchase Order berhasil diterbitkan.');
    }

    /**
     * Requester: Download PO PDF.
     * Only available when ticket status is 'po_generated'.
     */
    public function download(Request $request, Ticket $ticket): Response|RedirectResponse
    {
        $user = $request->user();

        // Only the ticket's requester can download
        if ($ticket->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses untuk mengunduh Purchase Order ini.');
        }

        if (! $ticket->isPoGenerated()) {
            abort(422, 'Purchase Order belum tersedia untuk tiket ini.');
        }

        if (! $ticket->document_po_path) {
            abort(404, 'File Purchase Order tidak ditemukan.');
        }

        // Stream the file from Supabase Storage
        $content = Storage::disk('s3')->get($ticket->document_po_path);

        $filename = basename($ticket->document_po_path);

        return response($content, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
