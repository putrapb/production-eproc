<?php

namespace App\Http\Controllers;

use App\Models\ApprovalLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display a listing of approval logs.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $action = $request->query('action');

        $query = ApprovalLog::with(['ticket', 'user.hrEmployee']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('ticket', function ($t) use ($search) {
                    $cleanSearch = ltrim($search, '#');
                    $t->where('title', 'like', '%' . $search . '%');
                    if (is_numeric($cleanSearch)) {
                        $t->orWhere('id', (int)$cleanSearch);
                    }
                })->orWhereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', '%' . $search . '%')
                      ->orWhereHas('hrEmployee', function ($h) use ($search) {
                          $h->where('nip', 'like', '%' . $search . '%');
                      });
                });
            });
        }

        if ($action) {
            $query->where('action', $action);
        }

        $logs = $query->latest()->paginate(15)->withQueryString();

        // Get actions from ApprovalLog constants to populate dropdown
        $actionOptions = [
            ApprovalLog::ACTION_SUBMITTED            => 'Tiket diajukan',
            ApprovalLog::ACTION_FOLLOWED_UP          => 'Dokumen diterima oleh Team Leader',
            ApprovalLog::ACTION_REJECTED_DOCUMENT    => 'Dokumen ditolak oleh Team Leader',
            ApprovalLog::ACTION_REVISED              => 'Dokumen direvisi oleh Requester',
            ApprovalLog::ACTION_VALIDATED            => 'Smart Validation berhasil',
            ApprovalLog::ACTION_CROSS_FUND_REQUESTED => 'Silang dana diajukan',
            ApprovalLog::ACTION_FORWARDED            => 'Diteruskan ke Department Head',
            ApprovalLog::ACTION_APPROVED             => 'Pengadaan disetujui',
            ApprovalLog::ACTION_DECLINED             => 'Pengadaan ditolak',
            ApprovalLog::ACTION_PO_ISSUED            => 'Purchase Order diterbitkan',
            ApprovalLog::ACTION_FORM_ISSUED          => 'Form Pengadaan diterbitkan',
        ];

        return view('audit-logs.index', compact('logs', 'search', 'action', 'actionOptions'));
    }
}
