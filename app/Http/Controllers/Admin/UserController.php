<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $authUser = $request->user();

        $users = User::query()
            ->where('company_id', $authUser->company_id)
            ->whereDoesntHave('roles', fn($query) => $query->where('name', 'admin'))
            ->latest()
            ->get();

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $authUser = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'status' => ['required', 'in:active,inactive,pending'],
        ]);

        DB::transaction(function () use ($validated, $authUser) {
            $user = User::create([
                'company_id' => $authUser->company_id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => $validated['status'],
            ]);

            $userRole = Role::firstOrCreate(['name' => 'user']);
            $user->roles()->syncWithoutDetaching([$userRole->id]);
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(Request $request, int $user): View
    {
        $user = $this->ownedNonAdminUser($request, $user);

        return view('admin.users.edit', [
            'managedUser' => $user,
        ]);
    }

    public function update(Request $request, int $user): RedirectResponse
    {
        $user = $this->ownedNonAdminUser($request, $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'status' => ['required', 'in:active,inactive,pending'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'status' => $validated['status'],
            'password' => !empty($validated['password']) ? Hash::make($validated['password']) : $user->password,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, int $user): RedirectResponse
    {
        $user = $this->ownedNonAdminUser($request, $user);

        DB::transaction(function () use ($user) {
            $user->roles()->detach();
            $user->delete();
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    private function ownedNonAdminUser(Request $request, int $userId): User
    {
        return User::query()
            ->where('id', $userId)
            ->where('company_id', $request->user()->company_id)
            ->whereDoesntHave('roles', fn($query) => $query->where('name', 'admin'))
            ->firstOrFail();
    }
}
