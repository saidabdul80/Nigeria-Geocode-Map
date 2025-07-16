<?php

namespace App\Http\Controllers;
// app/Http/Controllers/UserController.php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\State;
use App\Models\Lga;
use App\Models\Ward;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    use    AuthorizesRequests;
    public function index()
    {

        $this->authorize('manage_users');
        
        return Inertia::render('Users/Index', [
            'users' => User::with(['roles', 'statePermissions', 'lgaPermissions','wardPermissions'])
                ->latest()
                ->paginate(10),
            'roles' => Role::all(),
            'states' => State::with([
                'lgas' => function($query) {
                $query->select('id', 'name', 'state_id')
                      ->withCount('wards');  // Helpful for UI indicators
            }
        ])->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('manage_users');
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed' ],
            'roles' => 'required|array',
            'state_permissions' => 'nullable|array',
            'lga_permissions' => 'nullable|array',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->roles()->sync($request->roles);
        
        if ($request->state_permissions) {
            $user->statePermissions()->sync($request->state_permissions);
        }
        
        if ($request->lga_permissions) {
            $user->lgaPermissions()->sync($request->lga_permissions);
        }

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('manage_users');
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|confirmed',
            'roles' => 'required|array',
            'state_permissions' => 'nullable|array',
            'lga_permissions' => 'nullable|array',
            'ward_permissions' => 'nullable|array',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->roles()->sync($request->roles);
        $user->statePermissions()->sync($request->state_permissions ?? []);
        $user->lgaPermissions()->sync($request->lga_permissions ?? []);
        if ($request->ward_permissions) {
            $user->wardPermissions()->sync($request->ward_permissions);
        }
        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->authorize('manage_users');
        
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}