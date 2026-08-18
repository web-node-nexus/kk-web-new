<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('attendance_records', function (Blueprint $table) {
                $table->unique(['employee_id', 'date'], 'attendance_employee_date_unique');
            });
        } catch (\Throwable) {
            // Index may already exist
        }
    }

    public function down(): void
    {
        try {
            Schema::table('attendance_records', function (Blueprint $table) {
                $table->dropUnique('attendance_employee_date_unique');
            });
        } catch (\Throwable) {
        }
    }
};
