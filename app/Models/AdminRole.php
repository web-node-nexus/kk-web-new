<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminRole extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'permissions',
        'users_count',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_system' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'admin_role_id');
    }

    /** Full-access Admin role (not limited HR/Accounts roles). */
    public function hasFullAccess(): bool
    {
        $perms = $this->permissionList();

        return in_array('*', $perms, true) || $this->slug === 'admin';
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->hasFullAccess()) {
            return true;
        }

        return in_array($permission, $this->permissionList(), true);
    }

    /** @return list<string> */
    public function permissionList(): array
    {
        $perms = $this->permissions;
        if (is_string($perms)) {
            $decoded = json_decode($perms, true);
            if (is_array($decoded)) {
                $perms = $decoded;
            } elseif ($perms === '*') {
                $perms = ['*'];
            } else {
                $perms = [$perms];
            }
        }

        if (! is_array($perms)) {
            return [];
        }

        return array_values(array_filter($perms, fn ($key) => is_string($key) && $key !== ''));
    }

    public function refreshUsersCount(): void
    {
        $this->update(['users_count' => $this->users()->count()]);
    }

    public static function fullAccessRole(): ?self
    {
        return static::query()->where('slug', 'admin')->first();
    }

    public static function employeeRole(): ?self
    {
        return static::query()->where('slug', 'employee')->first();
    }
}
