<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $super = DB::table('admin_roles')->where('slug', 'super-admin')->first();
        $admin = DB::table('admin_roles')->where('slug', 'admin')->first();

        if ($super && ! $admin) {
            DB::table('admin_roles')->where('id', $super->id)->update([
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Full access to the entire admin panel',
                'permissions' => json_encode(['*']),
                'is_system' => true,
                'updated_at' => now(),
            ]);
        } elseif ($super && $admin) {
            // Move users from super-admin → admin, then delete super-admin
            DB::table('users')
                ->where('admin_role_id', $super->id)
                ->update(['admin_role_id' => $admin->id]);

            DB::table('admin_roles')->where('id', $admin->id)->update([
                'name' => 'Admin',
                'description' => 'Full access to the entire admin panel',
                'permissions' => json_encode(['*']),
                'is_system' => true,
                'users_count' => DB::table('users')->where('admin_role_id', $admin->id)->count(),
                'updated_at' => now(),
            ]);

            DB::table('admin_roles')->where('id', $super->id)->delete();
        } elseif (! $admin) {
            $adminId = DB::table('admin_roles')->insertGetId([
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Full access to the entire admin panel',
                'permissions' => json_encode(['*']),
                'users_count' => 0,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('users')
                ->where('role', 'admin')
                ->whereNull('admin_role_id')
                ->update(['admin_role_id' => $adminId]);

            DB::table('admin_roles')->where('id', $adminId)->update([
                'users_count' => DB::table('users')->where('admin_role_id', $adminId)->count(),
            ]);
        } else {
            DB::table('admin_roles')->where('id', $admin->id)->update([
                'name' => 'Admin',
                'description' => 'Full access to the entire admin panel',
                'permissions' => json_encode(['*']),
                'is_system' => true,
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('admin_roles')->where('slug', 'admin')->update([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'updated_at' => now(),
        ]);
    }
};
