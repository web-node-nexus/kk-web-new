<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120);
                $table->string('code', 40)->nullable();
                $table->string('head_name')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        Schema::table('job_openings', function (Blueprint $table) {
            if (! Schema::hasColumn('job_openings', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('id');
            }
            if (! Schema::hasColumn('job_openings', 'department_name')) {
                $table->string('department_name')->nullable()->after('title');
            }
            if (! Schema::hasColumn('job_openings', 'openings_count')) {
                $table->unsignedInteger('openings_count')->default(1)->after('is_open');
            }
        });

        if (! Schema::hasTable('interviews')) {
            Schema::create('interviews', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('job_application_id')->nullable();
                $table->string('candidate_name');
                $table->string('position')->nullable();
                $table->string('interviewer')->nullable();
                $table->dateTime('scheduled_at');
                $table->string('mode')->default('Online');
                $table->string('status')->default('scheduled');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('department_id')->nullable();
                $table->string('employee_code', 40)->nullable();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('phone')->nullable();
                $table->string('role_title')->nullable();
                $table->string('employment_type')->default('Full-time');
                $table->date('join_date')->nullable();
                $table->decimal('salary', 12, 2)->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('attendance_records')) {
            Schema::create('attendance_records', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->date('date');
                $table->time('check_in')->nullable();
                $table->time('check_out')->nullable();
                $table->string('status')->default('present');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('leave_requests')) {
            Schema::create('leave_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->string('leave_type')->default('Casual');
                $table->date('start_date');
                $table->date('end_date');
                $table->unsignedSmallInteger('days')->default(1);
                $table->string('status')->default('pending');
                $table->text('reason')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('payroll_records')) {
            Schema::create('payroll_records', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->string('month');
                $table->decimal('basic', 12, 2)->default(0);
                $table->decimal('allowances', 12, 2)->default(0);
                $table->decimal('deductions', 12, 2)->default(0);
                $table->decimal('net_pay', 12, 2)->default(0);
                $table->string('status')->default('pending');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('banners')) {
            Schema::create('banners', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('subtitle')->nullable();
                $table->string('cta_label')->nullable();
                $table->string('cta_url')->nullable();
                $table->string('image_path')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('testimonials')) {
            Schema::create('testimonials', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('role')->nullable();
                $table->string('company')->nullable();
                $table->text('quote');
                $table->unsignedTinyInteger('rating')->default(5);
                $table->boolean('is_published')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('cms_pages')) {
            Schema::create('cms_pages', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug', 160)->unique();
                $table->string('status')->default('draft');
                $table->longText('content')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('announcements')) {
            Schema::create('announcements', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('body');
                $table->string('audience')->default('All');
                $table->string('status')->default('draft');
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('support_tickets')) {
            Schema::create('support_tickets', function (Blueprint $table) {
                $table->id();
                $table->string('ticket_no', 40)->unique();
                $table->string('subject');
                $table->string('requester_name')->nullable();
                $table->string('requester_email')->nullable();
                $table->string('priority')->default('medium');
                $table->string('status')->default('open');
                $table->text('message')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('admin_roles')) {
            Schema::create('admin_roles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug', 80)->unique();
                $table->text('permissions')->nullable();
                $table->unsignedInteger('users_count')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_roles');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('cms_pages');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('payroll_records');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('interviews');
        Schema::table('job_openings', function (Blueprint $table) {
            if (Schema::hasColumn('job_openings', 'department_id')) {
                $table->dropColumn('department_id');
            }
            if (Schema::hasColumn('job_openings', 'department_name')) {
                $table->dropColumn('department_name');
            }
            if (Schema::hasColumn('job_openings', 'openings_count')) {
                $table->dropColumn('openings_count');
            }
        });
        Schema::dropIfExists('departments');
    }
};
