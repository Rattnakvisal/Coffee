<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * @var list<string>
     */
    private array $managedRoleSlugs = ['admin', 'cashier'];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $users = User::query()
            ->with('role')
            ->whereHas('role', function ($query): void {
                $query->whereIn('slug', $this->managedRoleSlugs);
            })
            ->when(
                $search !== '',
                function ($query) use ($search): void {
                    $query->where(function ($userQuery) use ($search): void {
                        $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                },
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => $this->activeManagedRoles(),
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role_id' => [
                'required',
                Rule::exists('roles', 'id')->where(function ($query): void {
                    $query
                        ->where('is_active', true)
                        ->whereIn('slug', $this->managedRoleSlugs);
                }),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'password' => $validated['password'],
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('alert', [
                'icon' => 'success',
                'title' => 'Member Added',
                'text' => 'New member has been created successfully.',
            ]);
    }

    public function suggestions(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));

        if ($search === '') {
            return response()->json([
                'data' => [],
            ]);
        }

        $data = User::query()
            ->whereHas('role', function ($query): void {
                $query->whereIn('slug', $this->managedRoleSlugs);
            })
            ->where(function ($query) use ($search): void {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'name', 'email'])
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ])
            ->values();

        return response()->json([
            'data' => $data,
        ]);
    }

    public function edit(User $user): View
    {
        $this->abortIfUserRoleNotManaged($user);

        return view('admin.users.edit', [
            'member' => $user,
            'roles' => $this->activeManagedRoles(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->abortIfUserRoleNotManaged($user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role_id' => [
                'required',
                Rule::exists('roles', 'id')->where(function ($query): void {
                    $query
                        ->where('is_active', true)
                        ->whereIn('slug', $this->managedRoleSlugs);
                }),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role_id = $validated['role_id'];

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('alert', [
                'icon' => 'success',
                'title' => 'Member Updated',
                'text' => 'Member details were updated successfully.',
            ]);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->abortIfUserRoleNotManaged($user);

        if ($request->user()->is($user)) {
            return redirect()
                ->route('admin.users.index')
                ->with('alert', [
                    'icon' => 'error',
                    'title' => 'Delete Blocked',
                    'text' => 'You cannot delete your own account.',
                ]);
        }

        if ($user->role?->slug === 'admin') {
            $adminCount = User::query()
                ->whereHas('role', function ($query): void {
                    $query->where('slug', 'admin');
                })
                ->count();

            if ($adminCount <= 1) {
                return redirect()
                    ->route('admin.users.index')
                    ->with('alert', [
                        'icon' => 'error',
                        'title' => 'Delete Blocked',
                        'text' => 'At least one admin account must remain.',
                    ]);
            }
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('alert', [
                'icon' => 'success',
                'title' => 'Member Deleted',
                'text' => 'Member has been deleted successfully.',
            ]);
    }

    private function activeManagedRoles(): Collection
    {
        return Role::query()
            ->active()
            ->whereIn('slug', $this->managedRoleSlugs)
            ->orderBy('name')
            ->get();
    }

    private function abortIfUserRoleNotManaged(User $user): void
    {
        $user->loadMissing('role');

        abort_unless(
            in_array($user->role?->slug, $this->managedRoleSlugs, true),
            404,
        );
    }
}
