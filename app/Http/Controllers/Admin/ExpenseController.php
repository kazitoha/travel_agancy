<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accounts;
use App\Models\Expenses;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        $accounts = Accounts::query()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $expenses = Expenses::query()
            ->where('user_id', $userId)
            ->with('account:id,name,type')
            ->latest('spent_at')
            ->limit(50)
            ->get();

        return view('admin.expenses.index', [
            'accounts' => $accounts,
            'expenses' => $expenses,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = $request->user()->id;

        $validated = $request->validate([
            'account_id' => [
                'required',
                Rule::exists('accounts', 'id')->where(fn($query) => $query
                    ->where('user_id', $userId)
                    ->where('status', 'active')),
            ],
            'amount' => ['required', 'numeric', 'gt:0'],
            'category' => ['required', 'string', 'max:100'],
            'spent_at' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
        ]);

        $attachmentPath = $request->file('attachment')?->store('expenses/attachments', 'public');

        DB::transaction(function () use ($validated, $userId, $attachmentPath) {
            $account = Accounts::query()
                ->where('id', $validated['account_id'])
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            Expenses::create([
                'user_id' => $userId,
                'account_id' => $account->id,
                'amount' => $validated['amount'],
                'category' => $validated['category'],
                'spent_at' => $validated['spent_at'],
                'note' => $validated['note'] ?? null,
                'attachment_path' => $attachmentPath,
            ]);

            $account->decrement('current_balance', $validated['amount']);
        });

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense saved and account balance updated.');
    }

    public function edit(Request $request, int $expense): View
    {
        $userId = $request->user()->id;
        $expense = $this->ownedExpense($request, $expense);

        $accounts = Accounts::query()
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get();

        return view('admin.expenses.edit', [
            'expense' => $expense,
            'accounts' => $accounts,
        ]);
    }

    public function update(Request $request, int $expense): RedirectResponse
    {
        $userId = $request->user()->id;
        $expense = $this->ownedExpense($request, $expense);

        $validated = $request->validate([
            'account_id' => [
                'required',
                Rule::exists('accounts', 'id')->where(fn($query) => $query->where('user_id', $userId)),
            ],
            'amount' => ['required', 'numeric', 'gt:0'],
            'category' => ['required', 'string', 'max:100'],
            'spent_at' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
        ]);

        $newAttachmentPath = $request->file('attachment')?->store('expenses/attachments', 'public');
        $oldAttachmentPath = $expense->attachment_path;

        DB::transaction(function () use ($validated, $userId, $expense, $newAttachmentPath) {
            $oldAccount = Accounts::query()
                ->where('id', $expense->account_id)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $expense->account_id === (int) $validated['account_id']) {
                $oldAccount->increment('current_balance', $expense->amount);
                $oldAccount->decrement('current_balance', $validated['amount']);
            } else {
                $newAccount = Accounts::query()
                    ->where('id', $validated['account_id'])
                    ->where('user_id', $userId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $oldAccount->increment('current_balance', $expense->amount);
                $newAccount->decrement('current_balance', $validated['amount']);
            }

            $expense->update([
                'account_id' => $validated['account_id'],
                'amount' => $validated['amount'],
                'category' => $validated['category'],
                'spent_at' => $validated['spent_at'],
                'note' => $validated['note'] ?? null,
                'attachment_path' => $newAttachmentPath ?? $expense->attachment_path,
            ]);
        });

        if ($newAttachmentPath && $oldAttachmentPath && $newAttachmentPath !== $oldAttachmentPath) {
            Storage::disk('public')->delete($oldAttachmentPath);
        }

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense updated and account balance adjusted.');
    }

    public function destroy(Request $request, int $expense): RedirectResponse
    {
        $userId = $request->user()->id;
        $expense = $this->ownedExpense($request, $expense);
        $attachmentPath = $expense->attachment_path;

        DB::transaction(function () use ($expense, $userId) {
            $account = Accounts::query()
                ->where('id', $expense->account_id)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            $account->increment('current_balance', $expense->amount);
            $expense->delete();
        });

        if ($attachmentPath) {
            Storage::disk('public')->delete($attachmentPath);
        }

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense deleted and account balance restored.');
    }

    private function ownedExpense(Request $request, int $expenseId): Expenses
    {
        return Expenses::query()
            ->where('id', $expenseId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
    }
}
