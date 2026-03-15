<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accounts;
use App\Models\Expenses;
use App\Models\TicketPurchasePaymentHistory;
use App\Models\TicketSalesPaymentHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $range = (string) $request->query('range', '7d');
        $scope = (string) $request->query('scope', 'all');

        $now = now();
        $rangeStart = null;

        if ($range === '7d') {
            $rangeStart = $now->copy()->subDays(6)->startOfDay();
        } elseif ($range === '30d') {
            $rangeStart = $now->copy()->subDays(29)->startOfDay();
        } elseif ($range === 'month') {
            $rangeStart = $now->copy()->startOfMonth();
        }

        $accounts = Accounts::query()
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'current_balance']);

        $totalAccountBalance = (float) $accounts->sum('current_balance');

        $salesQuery = TicketSalesPaymentHistory::query();
        if ($rangeStart) {
            $salesQuery->whereBetween('created_at', [$rangeStart, $now]);
        }
        $totalIncome = (float) $salesQuery->sum('paid');

        $companyId = Session::get('company_id');
        $purchasePaidTotal = 0.0;

        if ($companyId) {
            $purchaseQuery = TicketPurchasePaymentHistory::query()
                ->where('company_id', $companyId);
            if ($rangeStart) {
                $purchaseQuery->whereBetween('created_at', [$rangeStart, $now]);
            }
            $purchasePaidTotal = (float) $purchaseQuery->sum('paid');
        }

        $expenseQuery = Expenses::query();
        if ($rangeStart) {
            $expenseQuery->whereBetween('created_at', [$rangeStart, $now]);
        }
        $totalExpense = (float) $expenseQuery->sum('amount') + $purchasePaidTotal;

        $recentIncomeQuery = TicketSalesPaymentHistory::query()
            ->with(['account:id,name,type'])
            ->orderByDesc('created_at');
        if ($rangeStart) {
            $recentIncomeQuery->whereBetween('created_at', [$rangeStart, $now]);
        }
        $recentIncome = $recentIncomeQuery->limit(6)->get();

        $recentExpensesQuery = Expenses::query()
            ->with(['account:id,name,type'])
            ->orderByDesc('spent_at');
        if ($rangeStart) {
            $recentExpensesQuery->whereBetween('created_at', [$rangeStart, $now]);
        }
        $recentExpenses = $recentExpensesQuery->limit(6)->get();

        return view('admin.dashboard', [
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'totalAccountBalance' => $totalAccountBalance,
            'accounts' => $accounts,
            'range' => $range,
            'scope' => $scope,
            'recentIncome' => $recentIncome,
            'recentExpenses' => $recentExpenses,
        ]);
    }
}
