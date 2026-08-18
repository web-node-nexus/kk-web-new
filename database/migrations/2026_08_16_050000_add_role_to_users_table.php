<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role', 20)->default('admin')->after('email');
            }
            if (! Schema::hasColumn('users', 'employee_id')) {
                $table->unsignedBigInteger('employee_id')->nullable()->after('role');
            }
        });

        // Existing accounts stay admin
        DB::table('users')->whereNull('role')->orWhere('role', '')->update(['role' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'employee_id')) {
                $table->dropColumn('employee_id');
            }
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
