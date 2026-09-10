<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_records', function (Blueprint $table) {
            if (! Schema::hasColumn('payroll_records', 'issue_date')) {
                $table->date('issue_date')->nullable()->after('month');
            }
        });

        Schema::table('announcements', function (Blueprint $table) {
            if (! Schema::hasColumn('announcements', 'image_path')) {
                $table->string('image_path')->nullable()->after('body');
            }
            if (! Schema::hasColumn('announcements', 'link_url')) {
                $table->string('link_url', 500)->nullable()->after('image_path');
            }
        });

        if (! Schema::hasTable('client_project_members')) {
            Schema::create('client_project_members', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('client_project_id');
                $table->unsignedBigInteger('employee_id');
                $table->string('role_label', 120)->nullable();
                $table->timestamps();
                $table->unique(['client_project_id', 'employee_id']);
                $table->index('client_project_id');
                $table->index('employee_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('client_project_members');

        Schema::table('announcements', function (Blueprint $table) {
            if (Schema::hasColumn('announcements', 'link_url')) {
                $table->dropColumn('link_url');
            }
            if (Schema::hasColumn('announcements', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });

        Schema::table('payroll_records', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_records', 'issue_date')) {
                $table->dropColumn('issue_date');
            }
        });
    }
};
