<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accounts;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('account-logos', 'public');
        }

        Accounts::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'opening_balance' => $validated['opening_balance'],
            'current_balance' => $validated['opening_balance'],
            'status' => $validated['status'],
            'logo' => $logoPath,
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
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $previousOpening = (float) $account->opening_balance;
        $newOpening = (float) $validated['opening_balance'];
        $openingDelta = $newOpening - $previousOpening;

        $logoPath = $account->logo;
        if ($request->hasFile('logo')) {
            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }
            $logoPath = $request->file('logo')->store('account-logos', 'public');
        }

        $account->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'opening_balance' => $newOpening,
            'current_balance' => (float) $account->current_balance + $openingDelta,
            'status' => $validated['status'],
            'logo' => $logoPath,
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

        if ($account->logo) {
            Storage::disk('public')->delete($account->logo);
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
