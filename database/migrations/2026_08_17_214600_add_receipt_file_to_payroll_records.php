<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_records', function (Blueprint $table) {
            if (! Schema::hasColumn('payroll_records', 'receipt_path')) {
                $table->string('receipt_path')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('payroll_records', 'receipt_name')) {
                $table->string('receipt_name', 255)->nullable()->after('receipt_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payroll_records', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_records', 'receipt_name')) {
                $table->dropColumn('receipt_name');
            }
            if (Schema::hasColumn('payroll_records', 'receipt_path')) {
                $table->dropColumn('receipt_path');
            }
        });
    }
};
