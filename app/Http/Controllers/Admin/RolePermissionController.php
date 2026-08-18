<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use App\Models\User;
use App\Support\AdminPermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RolePermissionController extends Controller
{
    public function index(): View
    {
        $this->authorizeManage();

        $roles = AdminRole::query()
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get();

        $memberCounts = User::query()
            ->selectRaw('admin_role_id, COUNT(*) as total')
            ->whereNotNull('admin_role_id')
            ->groupBy('admin_role_id')
            ->pluck('total', 'admin_role_id');

        foreach ($roles as $role) {
            $role->setAttribute('users_count', (int) ($memberCounts[$role->id] ?? 0));
        }

        $admins = User::query()
            ->where('role', User::ROLE_ADMIN)
            ->with('adminRole')
            ->orderBy('name')
            ->get();

        $employees = User::query()
            ->where('role', User::ROLE_EMPLOYEE)
            ->with(['adminRole', 'employee'])
            ->orderBy('name')
            ->get();

        return view('admin.roles.index', [
            'user' => Auth::user(),
            'roles' => $roles,
            'admins' => $admins,
            'employees' => $employees,
            'catalog' => AdminPermissions::catalog(),
        ]);
    }

    public function create(): View
    {
        $this->authorizeManage();

        return view('admin.roles.form', [
            'user' => Auth::user(),
            'role' => null,
            'mode' => 'create',
            'catalog' => AdminPermissions::catalog(),
            'selected' => [],
            'admins' => User::query()->where('role', User::ROLE_ADMIN)->orderBy('name')->get(),
            'employees' => User::query()->where('role', User::ROLE_EMPLOYEE)->with('employee')->orderBy('name')->get(),
            'assignedIds' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManage();

        $data = $this->validatedRole($request);
        $role = AdminRole::query()->create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'permissions' => $data['permissions'],
            'is_system' => false,
            'users_count' => 0,
        ]);

        $this->syncUsers($role, $data['user_ids'] ?? []);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role "'.$role->name.'" created.');
    }

    public function edit(int $id): View
    {
        $this->authorizeManage();

        $role = AdminRole::query()->with('users')->findOrFail($id);

        return view('admin.roles.form', [
            'user' => Auth::user(),
            'role' => $role,
            'mode' => 'edit',
            'catalog' => AdminPermissions::catalog(),
            'selected' => $role->hasFullAccess() ? AdminPermissions::allKeys() : $role->permissionList(),
            'admins' => User::query()->where('role', User::ROLE_ADMIN)->orderBy('name')->get(),
            'employees' => User::query()->where('role', User::ROLE_EMPLOYEE)->with('employee')->orderBy('name')->get(),
            'assignedIds' => $role->users->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->authorizeManage();

        $role = AdminRole::query()->findOrFail($id);
        $data = $this->validatedRole($request, $role);

        if ($role->hasFullAccess() || $role->slug === 'admin') {
            // Full-access Admin role — keep all permissions
            $role->update([
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => $data['description'] ?? $role->description,
                'permissions' => ['*'],
            ]);
        } else {
            $role->update([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'permissions' => $data['permissions'],
            ]);
        }

        $this->syncUsers($role, $data['user_ids'] ?? []);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role "'.$role->name.'" updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeManage();

        $role = AdminRole::query()->findOrFail($id);
        if ($role->hasFullAccess() || $role->slug === 'admin' || $role->slug === 'employee' || $role->is_system) {
            return back()->withErrors(['role' => 'This role cannot be deleted.']);
        }

        $fallback = $role->slug === 'employee'
            ? AdminRole::fullAccessRole()
            : (AdminRole::employeeRole() ?? AdminRole::fullAccessRole());
        User::query()->where('admin_role_id', $role->id)->get()->each(function (User $user) use ($fallback) {
            $user->update([
                'admin_role_id' => $user->isEmployee()
                    ? AdminRole::employeeRole()?->id
                    : $fallback?->id,
            ]);
        });
        $role->delete();
        $fallback?->refreshUsersCount();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role deleted. Users moved to Admin.');
    }

    public function createUser(Request $request): RedirectResponse
    {
        $this->authorizeManage();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'max:100'],
            'admin_role_id' => ['required', 'integer', 'exists:admin_roles,id'],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_ADMIN,
            'admin_role_id' => $data['admin_role_id'],
        ]);

        AdminRole::query()->find($data['admin_role_id'])?->refreshUsersCount();

        return back()->with('success', 'Admin user "'.$user->name.'" created and role assigned.');
    }

    public function assign(Request $request): RedirectResponse
    {
        $this->authorizeManage();

        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'admin_role_id' => ['required', 'integer', 'exists:admin_roles,id'],
        ]);

        $user = User::query()->findOrFail($data['user_id']);
        $newRole = AdminRole::query()->findOrFail($data['admin_role_id']);

        if ($user->isAdmin() && $user->isSoleAdmin() && ! $newRole->hasFullAccess()) {
            return back()->withErrors([
                'role' => 'Ye main admin account hai. Limited role dene se Roles page lock ho jata hai. Pehle doosra Admin banao, ya isko Admin (full access) role do.',
            ]);
        }

        $oldRoleId = $user->admin_role_id;
        $user->update(['admin_role_id' => $data['admin_role_id']]);

        if ($oldRoleId) {
            AdminRole::query()->find($oldRoleId)?->refreshUsersCount();
        }
        AdminRole::query()->find($data['admin_role_id'])?->refreshUsersCount();

        return back()->with('success', $user->name.' role updated. Employee panel me yahi permissions dikhengi.');
    }

    /** @return array<string, mixed> */
    protected function validatedRole(Request $request, ?AdminRole $role = null): array
    {
        $isFullAdmin = $role && ($role->hasFullAccess() || $role->slug === 'admin');

        $allowed = AdminPermissions::allKeys();
        $incoming = $request->input('permissions', []);
        if (! is_array($incoming)) {
            $incoming = [];
        }
        $request->merge([
            'permissions' => array_values(array_intersect($incoming, $allowed)),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'nullable',
                'string',
                'max:80',
                Rule::unique('admin_roles', 'slug')->ignore($role?->id),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => [$isFullAdmin ? 'nullable' : 'required', 'array', 'min:1'],
            'permissions.*' => ['string'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'full_access' => ['nullable', 'boolean'],
        ], [
            'permissions.required' => 'Kam se kam 1 permission tick karo.',
            'permissions.min' => 'Kam se kam 1 permission tick karo.',
        ]);

        $slug = trim((string) ($data['slug'] ?? ''));
        if ($slug === '') {
            $slug = Str::slug($data['name']);
        }
        if ($slug === '') {
            $slug = 'role-'.Str::lower(Str::random(6));
        }
        // Never allow creating another slug "admin" accidentally
        if (! $isFullAdmin && $slug === 'admin') {
            $slug = 'admin-'.Str::lower(Str::random(4));
        }
        $data['slug'] = $slug;

        if (! empty($request->boolean('full_access')) || $isFullAdmin) {
            $data['permissions'] = ['*'];
        } else {
            $data['permissions'] = array_values(array_unique($data['permissions'] ?? []));
        }

        return $data;
    }

    /** @param list<int|string> $userIds */
    protected function syncUsers(AdminRole $role, array $userIds): void
    {
        $userIds = array_values(array_unique(array_map('intval', $userIds)));
        $current = User::query()->where('admin_role_id', $role->id)->pluck('id')->all();
        $toRemove = array_diff($current, $userIds);

        if ($toRemove) {
            $users = User::query()->whereIn('id', $toRemove)->get();
            foreach ($users as $user) {
                $fallback = $user->isEmployee()
                    ? AdminRole::employeeRole()?->id
                    : AdminRole::fullAccessRole()?->id;
                if ((int) $fallback === (int) $role->id) {
                    $fallback = null;
                }
                $user->update(['admin_role_id' => $fallback]);
            }
        }

        if ($userIds) {
            $assignIds = $userIds;
            if (! $role->hasFullAccess()) {
                $sole = User::query()->where('role', User::ROLE_ADMIN)->count() <= 1
                    ? User::query()->where('role', User::ROLE_ADMIN)->value('id')
                    : null;
                if ($sole) {
                    $assignIds = array_values(array_filter($assignIds, fn ($id) => (int) $id !== (int) $sole));
                }
            }
            if ($assignIds) {
                User::query()->whereIn('id', $assignIds)->update(['admin_role_id' => $role->id]);
            }
        }

        foreach (AdminRole::query()->get() as $r) {
            $r->refreshUsersCount();
        }
    }

    protected function authorizeManage(): void
    {
        /** @var User $user */
        $user = Auth::user();
        abort_unless($user && $user->hasPermission('roles.manage'), 403, 'You do not have permission to manage roles.');
    }
}
