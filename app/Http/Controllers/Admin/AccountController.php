<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accounts;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    private const ACCOUNT_TYPES = [
        'cash' => 'Cash',
        'bank' => 'Bank',
        'mobile' => 'Mobile Banking',
        'business_wallet' => 'Business Wallet',
    ];

    public function index(Request $request): View
    {
        $accounts = Accounts::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return view('admin.accounts.index', [
            'accounts' => $accounts,
            'accountTypes' => self::ACCOUNT_TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:' . implode(',', array_keys(self::ACCOUNT_TYPES))],
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        Accounts::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'opening_balance' => $validated['opening_balance'],
            'current_balance' => $validated['opening_balance'],
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Account created successfully.');
    }

    public function edit(Request $request, int $account): View
    {
        $account = $this->ownedAccount($request, $account);

        return view('admin.accounts.edit', [
            'account' => $account,
            'accountTypes' => self::ACCOUNT_TYPES,
        ]);
    }

    public function update(Request $request, int $account): RedirectResponse
    {
        $account = $this->ownedAccount($request, $account);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:' . implode(',', array_keys(self::ACCOUNT_TYPES))],
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $previousOpening = (float) $account->opening_balance;
        $newOpening = (float) $validated['opening_balance'];
        $openingDelta = $newOpening - $previousOpening;

        $account->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'opening_balance' => $newOpening,
            'current_balance' => (float) $account->current_balance + $openingDelta,
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Account updated successfully.');
    }

    public function destroy(Request $request, int $account): RedirectResponse
    {
        $account = $this->ownedAccount($request, $account);

        if ($account->expenses()->exists()) {
            return redirect()
                ->route('accounts.index')
                ->with('error', 'This account has expenses and cannot be deleted.');
        }

        $account->delete();

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Account deleted successfully.');
    }

    private function ownedAccount(Request $request, int $accountId): Accounts
    {
        return Accounts::query()
            ->where('id', $accountId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
    }
}
