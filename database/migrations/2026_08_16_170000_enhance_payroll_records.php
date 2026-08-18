<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_records', function (Blueprint $table) {
            if (! Schema::hasColumn('payroll_records', 'notes')) {
                $table->string('notes', 500)->nullable()->after('status');
            }
            if (! Schema::hasColumn('payroll_records', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('notes');
            }
        });

        try {
            Schema::table('payroll_records', function (Blueprint $table) {
                $table->unique(['employee_id', 'month'], 'payroll_employee_month_unique');
            });
        } catch (\Throwable) {
        }
    }

    public function down(): void
    {
        try {
            Schema::table('payroll_records', function (Blueprint $table) {
                $table->dropUnique('payroll_employee_month_unique');
            });
        } catch (\Throwable) {
        }

        Schema::table('payroll_records', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_records', 'paid_at')) {
                $table->dropColumn('paid_at');
            }
            if (Schema::hasColumn('payroll_records', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
