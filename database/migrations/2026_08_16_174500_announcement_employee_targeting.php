<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (! Schema::hasColumn('announcements', 'employee_id')) {
                $table->foreignId('employee_id')->nullable()->after('audience')
                    ->constrained('employees')->nullOnDelete();
            }
            if (! Schema::hasColumn('announcements', 'audience_type')) {
                $table->string('audience_type', 20)->default('all')->after('audience');
            }
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (Schema::hasColumn('announcements', 'employee_id')) {
                $table->dropConstrainedForeignId('employee_id');
            }
            if (Schema::hasColumn('announcements', 'audience_type')) {
                $table->dropColumn('audience_type');
            }
        });
    }
};
