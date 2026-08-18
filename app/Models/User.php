<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'employee_id', 'admin_role_id', 'avatar_path'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_EMPLOYEE = 'employee';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function adminRole(): BelongsTo
    {
        return $this->belongsTo(AdminRole::class, 'admin_role_id');
    }

    public function isAdmin(): bool
    {
        return ($this->role ?? self::ROLE_ADMIN) === self::ROLE_ADMIN;
    }

    public function isEmployee(): bool
    {
        return ($this->role ?? '') === self::ROLE_EMPLOYEE;
    }

    /**
     * Admins with no assigned role get full access.
     * Role slug "admin" (or permissions *) also gets full access.
     * The only admin account cannot be locked out of the panel.
     */
    public function hasPermission(string $permission): bool
    {
        if (! $this->isAdmin()) {
            return false;
        }

        if ($this->isSoleAdmin()) {
            return true;
        }

        if (! $this->admin_role_id) {
            return true;
        }

        $role = $this->relationLoaded('adminRole')
            ? $this->adminRole
            : $this->adminRole()->first();

        if (! $role) {
            return true;
        }

        return $role->hasPermission($permission);
    }

    public function isSoleAdmin(): bool
    {
        static $count = null;
        if ($count === null) {
            $count = static::query()->where('role', self::ROLE_ADMIN)->count();
        }

        return $count <= 1;
    }

    /**
     * Employee panel: sirf wahi modules jo role me Employee panel ke under tick hue.
     * Admin module.* ticks employee panel nahi kholte. No tick = module hide.
     */
    public function canEmployee(string $permission): bool
    {
        if (! $this->isEmployee()) {
            return false;
        }

        if (! in_array($permission, \App\Support\EmployeeAccess::defaultKeys(), true)) {
            return false;
        }

        $this->loadMissing('adminRole');
        $role = $this->adminRole;
        if (! $role) {
            return false;
        }

        if ($role->hasFullAccess()) {
            return true;
        }

        return $role->hasPermission($permission);
    }

    public function hasAnyEmployeeModule(): bool
    {
        foreach (\App\Support\EmployeeAccess::defaultKeys() as $key) {
            if ($this->canEmployee($key)) {
                return true;
            }
        }

        return false;
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function avatarUrl(): ?string
    {
        return \App\Support\TaskMedia::url($this->avatar_path);
    }

    public function initials(): string
    {
        $chars = collect(preg_split('/\s+/', trim((string) $this->name) ?: 'A'))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)));

        return $chars->implode('') ?: 'A';
    }
}
