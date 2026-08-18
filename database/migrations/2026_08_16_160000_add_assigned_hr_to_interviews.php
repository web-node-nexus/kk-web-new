<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            if (! Schema::hasColumn('interviews', 'assigned_hr_employee_id')) {
                $table->unsignedBigInteger('assigned_hr_employee_id')->nullable()->after('hr_email');
                $table->index('assigned_hr_employee_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            if (Schema::hasColumn('interviews', 'assigned_hr_employee_id')) {
                $table->dropColumn('assigned_hr_employee_id');
            }
        });
    }
};
