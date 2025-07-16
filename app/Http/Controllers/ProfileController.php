<?php
namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Illuminate\Validation\Rules;

class ProfileController extends Controller
{
    use    AuthorizesRequests;
    public function edit()
    {
        $user = auth()->user();
        $user->load(['statePermissions','lgaPermissions']);
        return Inertia::render('Profile/Edit', [
            'user' => $user
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'current_password' => 'nullable|required_with:password|current_password',
            'password' => 'nullable|confirmed|',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('profile.edit')
            ->with('success', 'Profile updated successfully.');
    }
}