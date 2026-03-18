<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.settings.index', [
            'member' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'remove_avatar' => ['nullable', Rule::in(['0', '1'])],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if (($validated['remove_avatar'] ?? '0') === '1') {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $user->avatar_path = null;
        } elseif ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
        }

        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];
        $user->name = trim($validated['first_name'] . ' ' . $validated['last_name']);
        $user->email = $validated['email'];
        $user->phone = filled($validated['phone'] ?? null) ? $validated['phone'] : null;
        $user->gender = filled($validated['gender'] ?? null) ? $validated['gender'] : null;
        $user->save();

        return redirect()
            ->route('admin.settings.index')
            ->with('activeSettingsTab', 'profile')
            ->with('alert', [
                'icon' => 'success',
                'title' => 'Profile Updated',
                'text' => 'Admin profile has been updated successfully.',
            ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ]);

        $user->password = $validated['password'];
        $user->save();

        return redirect()
            ->route('admin.settings.index')
            ->with('activeSettingsTab', 'security')
            ->with('alert', [
                'icon' => 'success',
                'title' => 'Password Updated',
                'text' => 'Your password has been changed successfully.',
            ]);
    }
}
