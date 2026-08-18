<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_tasks', function (Blueprint $table) {
            $table->dateTime('due_at')->nullable()->after('due_date');
        });

        DB::update("UPDATE work_tasks SET due_at = CONCAT(due_date, ' 18:00:00') WHERE due_date IS NOT NULL AND due_at IS NULL");

        Schema::create('employee_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('type', 40);
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'read_at']);
        });

        if (! DB::table('site_settings')->where('key', 'mail_task_assigned')->exists()) {
            DB::table('site_settings')->insert([
                'key' => 'mail_task_assigned',
                'value' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_notifications');
        Schema::table('work_tasks', function (Blueprint $table) {
            $table->dropColumn('due_at');
        });
    }
};
