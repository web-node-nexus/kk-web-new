<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('site_settings')) {
            Schema::create('site_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 100)->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        $defaults = [
            'company_name' => 'KK Digital Solution',
            'support_email' => 'support.kkdigitalsolution@gmail.com',
            'support_phone' => '+91 93709 21363 | +91 89319 35177',
            'company_address' => 'K & K Hub, Jalgaon Jamod, Maharashtra, 443402',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'default_hr_email' => env('HR_EMAIL', 'support.kkdigitalsolution@gmail.com'),
            'late_checkin_after' => '10:30',

            'feature_careers' => '1',
            'feature_contact_form' => '1',
            'feature_project_form' => '1',
            'feature_newsletter' => '0',
            'feature_announcements' => '1',
            'feature_employee_attendance' => '1',
            'feature_employee_leaves' => '1',
            'feature_employee_payroll' => '1',
            'feature_employee_interviews' => '1',

            'mail_application_status' => '1',
            'mail_leave_status' => '1',
            'mail_hr_interview' => '1',
            'mail_employee_welcome' => '1',
        ];

        foreach ($defaults as $key => $value) {
            if (! DB::table('site_settings')->where('key', $key)->exists()) {
                DB::table('site_settings')->insert([
                    'key' => $key,
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
