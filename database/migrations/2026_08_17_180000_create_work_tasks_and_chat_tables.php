<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority', 20)->default('normal');
            $table->date('due_date')->nullable();
            $table->string('status', 30)->default('open');
            $table->boolean('assign_all')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('work_task_assignees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_task_id')->constrained('work_tasks')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('status', 30)->default('pending');
            $table->timestamp('seen_at')->nullable();
            $table->timestamps();
            $table->unique(['work_task_id', 'employee_id']);
        });

        Schema::create('work_task_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_task_id')->constrained('work_tasks')->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sender_type', 20);
            $table->text('message');
            $table->timestamps();
        });

        Schema::create('live_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sender_type', 20);
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_chat_messages');
        Schema::dropIfExists('work_task_replies');
        Schema::dropIfExists('work_task_assignees');
        Schema::dropIfExists('work_tasks');
    }
};
