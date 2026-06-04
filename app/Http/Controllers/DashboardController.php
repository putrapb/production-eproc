<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the role-adaptive dashboard.
     *
     * Provides:
     *  - Ticket status summary counts (role-filtered)
     *  - Recent activity (latest tickets with activity)
     *  - Budget utilization per category (CAPEX + OPEX)
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // ─── Ticket Status Summary ───
        $ticketSummary = $this->buildTicketSummary($user);

        // ─── Recent Activity ───
        $recentTickets = Ticket::with(['user', 'approvalLogs.user'])
            ->forRole($user)
            ->latest('updated_at')
            ->limit(10)
            ->get();

        // ─── Budget Utilization (all roles see this) ───
        $budgetData = $this->buildBudgetUtilization();

        return view('dashboard.index', compact('ticketSummary', 'recentTickets', 'budgetData', 'user'));
    }

    private function buildTicketSummary($user): array
    {
        $base = Ticket::forRole($user);

        return match ($user->role) {
            'requester' => [
                'total'             => (clone $base)->count(),
                'pending_review'    => (clone $base)->where('status', Ticket::STATUS_PENDING_REVIEW)->count(),
                'need_to_validate'  => (clone $base)->where('status', Ticket::STATUS_NEED_TO_VALIDATE)->count(),
                'in_approval'       => (clone $base)->whereIn('status', [
                    Ticket::STATUS_PENDING_DEPT_HEAD,
                    Ticket::STATUS_PENDING_DIV_HEAD,
                ])->count(),
                'approved'          => (clone $base)->where('status', Ticket::STATUS_APPROVED)->count(),
                'po_generated'      => (clone $base)->where('status', Ticket::STATUS_PO_GENERATED)->count(),
                'declined'          => (clone $base)->where('status', Ticket::STATUS_DECLINED)->count(),
                'revision'          => (clone $base)->where('status', Ticket::STATUS_REVISION)->count(),
            ],
            'pfa' => [
                'pending_review'    => (clone $base)->where('status', Ticket::STATUS_PENDING_REVIEW)->count(),
                'approved'          => (clone $base)->where('status', Ticket::STATUS_APPROVED)->count(),
                'po_generated'      => (clone $base)->where('status', Ticket::STATUS_PO_GENERATED)->count(),
            ],
            'department_head' => [
                'pending_dept_head' => (clone $base)->where('status', Ticket::STATUS_PENDING_DEPT_HEAD)->count(),
                'pending_div_head'  => (clone $base)->where('status', Ticket::STATUS_PENDING_DIV_HEAD)->count(),
            ],
            'division_head' => [
                'pending_div_head'  => (clone $base)->where('status', Ticket::STATUS_PENDING_DIV_HEAD)->count(),
                'approved'          => (clone $base)->where('status', Ticket::STATUS_APPROVED)->count(),
                'declined'          => (clone $base)->where('status', Ticket::STATUS_DECLINED)->count(),
            ],
            default => [],
        };
    }

    private function buildBudgetUtilization(): array
    {
        $categories = array_keys(config('eprocurement.categories', []));
        $year       = now()->year;
        $result     = [];

        foreach ($categories as $category) {
            $capex = Budget::where('expenditure_type', 'CAPEX')
                ->where('category', $category)
                ->where('fiscal_year', $year)
                ->first();

            $opex = Budget::where('expenditure_type', 'OPEX')
                ->where('category', $category)
                ->where('fiscal_year', $year)
                ->first();

            $result[$category] = [
                'capex' => $capex ? [
                    'total_limit'            => $capex->total_limit,
                    'used_amount'            => $capex->used_amount,
                    'locked_amount'          => $capex->locked_amount,
                    'available_balance'      => $capex->available_balance,
                    'utilization_percentage' => $capex->utilization_percentage,
                ] : null,
                'opex'  => $opex ? [
                    'total_limit'            => $opex->total_limit,
                    'used_amount'            => $opex->used_amount,
                    'locked_amount'          => $opex->locked_amount,
                    'available_balance'      => $opex->available_balance,
                    'utilization_percentage' => $opex->utilization_percentage,
                ] : null,
            ];
        }

        return $result;
    }
}
