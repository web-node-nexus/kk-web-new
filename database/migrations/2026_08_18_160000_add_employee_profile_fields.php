<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'job_type')) {
                $table->string('job_type', 40)->nullable()->after('employment_type');
            }
            if (! Schema::hasColumn('employees', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'job_type')) {
                $table->dropColumn('job_type');
            }
            if (Schema::hasColumn('employees', 'photo_path')) {
                $table->dropColumn('photo_path');
            }
        });
    }
};
