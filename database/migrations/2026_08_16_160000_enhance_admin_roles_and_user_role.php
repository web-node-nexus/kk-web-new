<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_roles', function (Blueprint $table) {
            if (! Schema::hasColumn('admin_roles', 'description')) {
                $table->string('description')->nullable()->after('slug');
            }
            if (! Schema::hasColumn('admin_roles', 'is_system')) {
                $table->boolean('is_system')->default(false)->after('users_count');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'admin_role_id')) {
                $table->foreignId('admin_role_id')->nullable()->after('role')
                    ->constrained('admin_roles')->nullOnDelete();
            }
        });

            $superId = DB::table('admin_roles')->where('slug', 'admin')->value('id');
        if (! $superId) {
            $superId = DB::table('admin_roles')->insertGetId([
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Full access to the entire admin panel',
                'permissions' => json_encode(['*']),
                'users_count' => 0,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('admin_roles')->where('id', $superId)->update([
                'name' => 'Admin',
                'slug' => 'admin',
                'permissions' => json_encode(['*']),
                'is_system' => true,
                'description' => 'Full access to the entire admin panel',
                'updated_at' => now(),
            ]);
        }

        // Sample limited roles (only if missing)
        $samples = [
            [
                'name' => 'HR Manager',
                'slug' => 'hr-manager',
                'description' => 'Hiring, interviews, employees',
                'permissions' => [
                    'dashboard.view', 'overview.view',
                    'module.applications', 'module.internships', 'module.candidates',
                    'module.interviews', 'module.jobs', 'module.departments', 'module.employees',
                    'career-page.view',
                ],
            ],
            [
                'name' => 'Accounts',
                'slug' => 'accounts',
                'description' => 'Attendance, leaves, payroll',
                'permissions' => [
                    'dashboard.view',
                    'module.employees', 'module.attendance', 'module.leaves', 'module.payroll',
                ],
            ],
            [
                'name' => 'Support Desk',
                'slug' => 'support-desk',
                'description' => 'Contact, projects, tickets',
                'permissions' => [
                    'dashboard.view',
                    'module.contacts', 'module.projects', 'module.newsletter',
                    'module.announcements', 'module.tickets',
                ],
            ],
        ];

        foreach ($samples as $sample) {
            if (! DB::table('admin_roles')->where('slug', $sample['slug'])->exists()) {
                DB::table('admin_roles')->insert([
                    'name' => $sample['name'],
                    'slug' => $sample['slug'],
                    'description' => $sample['description'],
                    'permissions' => json_encode($sample['permissions']),
                    'users_count' => 0,
                    'is_system' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Existing admins without a role → Super Admin
        DB::table('users')
            ->where('role', 'admin')
            ->whereNull('admin_role_id')
            ->update(['admin_role_id' => $superId]);

        $count = DB::table('users')->where('admin_role_id', $superId)->count();
        DB::table('admin_roles')->where('id', $superId)->update(['users_count' => $count]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'admin_role_id')) {
                $table->dropConstrainedForeignId('admin_role_id');
            }
        });

        Schema::table('admin_roles', function (Blueprint $table) {
            if (Schema::hasColumn('admin_roles', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('admin_roles', 'is_system')) {
                $table->dropColumn('is_system');
            }
        });
    }
};
