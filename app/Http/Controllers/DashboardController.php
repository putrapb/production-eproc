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
            ->where('id', '!=', 5)
            ->where('title', '!=', 'Pengadaan Penambahan Kapasitas Storage BNI')
            ->latest('updated_at')
            ->limit(10)
            ->get();

        // ─── Budget Utilization (all roles see this) ───
        $budgetData = $this->buildBudgetUtilization();

        // ─── Chart Data (Trend & Composition) ───
        $year = now()->year;
        $monthlyTickets = Ticket::whereYear('created_at', $year)
            ->where('id', '!=', 5)
            ->where('title', '!=', 'Pengadaan Penambahan Kapasitas Storage BNI')
            ->get();
        
        $trendMonths = [];
        $trendTotals = [];
        for ($m = 1; $m <= 12; $m++) {
            $date = \Carbon\Carbon::create($year, $m, 1);
            $monthName = $date->isoFormat('MMMM');
            $trendMonths[] = $monthName;
            
            $total = $monthlyTickets->filter(fn($t) => $t->created_at->month === $m)->sum(fn($t) => $t->total_amount);
            $trendTotals[] = (float) $total;
        }

        $budgets = Budget::where('fiscal_year', $year)->get();
        $compositionCategories = [];
        $compositionValues = [];
        $categories = ['infrastruktur_utama', 'lisensi_sistem', 'layanan_pemeliharaan', 'perlengkapan_operasional'];
        
        foreach ($categories as $category) {
            $categoryBudgets = $budgets->where('category', $category);
            $totalUsed = $categoryBudgets->sum(fn($b) => (float)$b->used_amount + (float)$b->locked_amount);
            
            $compositionCategories[] = config('eprocurement.categories.' . $category, strtoupper(str_replace('_', ' ', $category)));
            $compositionValues[] = $totalUsed;
        }

        $chartData = [
            'trend' => [
                'labels' => $trendMonths,
                'data'   => $trendTotals,
            ],
            'composition' => [
                'labels' => $compositionCategories,
                'data'   => $compositionValues,
            ],
        ];

        return view('dashboard.index', compact('ticketSummary', 'recentTickets', 'budgetData', 'user', 'chartData'));
    }

    private function buildTicketSummary($user): array
    {
        $base = Ticket::forRole($user);

        return match ($user->role) {
            'requester' => [
                'total'             => (clone $base)->count(),
                'pending_review'    => (clone $base)->where('status', Ticket::STATUS_PENDING_REVIEW)->count(),
                'need_to_validate'  => (clone $base)->where('status', Ticket::STATUS_NEED_TO_VALIDATE)->count(),
                'in_approval'       => (clone $base)->where('status', Ticket::STATUS_PENDING_DEPT_HEAD)->count(),
                'approved'          => (clone $base)->where('status', Ticket::STATUS_APPROVED)->count(),
                'form_generated'    => (clone $base)->where('status', Ticket::STATUS_FORM_GENERATED)->count(),
                'declined'          => (clone $base)->where('status', Ticket::STATUS_DECLINED)->count(),
                'revision'          => (clone $base)->where('status', Ticket::STATUS_REVISION)->count(),
            ],
            'team_leader' => [
                'pending_review'    => (clone $base)->where('status', Ticket::STATUS_PENDING_REVIEW)->count(),
                'approved'          => (clone $base)->where('status', Ticket::STATUS_APPROVED)->count(),
                'form_generated'    => (clone $base)->where('status', Ticket::STATUS_FORM_GENERATED)->count(),
            ],
            'department_head' => [
                'pending_dept_head'   => (clone $base)->where('status', Ticket::STATUS_PENDING_DEPT_HEAD)->count(),
                'approved'            => (clone $base)->where('status', Ticket::STATUS_APPROVED)->count(),
                'declined'            => (clone $base)->where('status', Ticket::STATUS_DECLINED)->count(),
            ],
            default => [],
        };
    }

    private function buildBudgetUtilization(): array
    {
        $categories = array_keys(config('eprocurement.categories', []));
        $year       = now()->year;
        $result     = [];

        // N+1 Query Fix: Fetch all budgets for the year in a single query
        $budgets = Budget::where('fiscal_year', $year)->get();

        foreach ($categories as $category) {
            $capex = $budgets->first(fn($b) => $b->expenditure_type === 'CAPEX' && $b->category === $category);
            $opex  = $budgets->first(fn($b) => $b->expenditure_type === 'OPEX'  && $b->category === $category);

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
