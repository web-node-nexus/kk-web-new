<?php

use App\Models\AdminRole;
use App\Models\User;
use App\Support\EmployeeAccess;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $perms = json_encode(EmployeeAccess::defaultKeys());

        $exists = DB::table('admin_roles')->where('slug', 'employee')->first();
        if (! $exists) {
            $id = DB::table('admin_roles')->insertGetId([
                'name' => 'Employee',
                'slug' => 'employee',
                'description' => 'Default employee panel access — attendance, leaves, tasks, chat, and more.',
                'permissions' => $perms,
                'is_system' => true,
                'users_count' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $id = $exists->id;
            $current = json_decode((string) $exists->permissions, true) ?: [];
            if (! in_array('employee.attendance', $current, true) && ! in_array('*', $current, true)) {
                DB::table('admin_roles')->where('id', $id)->update([
                    'permissions' => $perms,
                    'updated_at' => $now,
                ]);
            }
        }

        User::query()
            ->where('role', User::ROLE_EMPLOYEE)
            ->whereNull('admin_role_id')
            ->update(['admin_role_id' => $id]);

        AdminRole::query()->find($id)?->refreshUsersCount();
    }

    public function down(): void
    {
        $role = DB::table('admin_roles')->where('slug', 'employee')->first();
        if (! $role) {
            return;
        }

        User::query()
            ->where('role', User::ROLE_EMPLOYEE)
            ->where('admin_role_id', $role->id)
            ->update(['admin_role_id' => null]);
    }
};
