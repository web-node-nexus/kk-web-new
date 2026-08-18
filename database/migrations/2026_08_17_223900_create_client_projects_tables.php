<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_projects', function (Blueprint $table) {
            $table->id();
            $table->string('logo_path')->nullable();
            $table->string('name');
            $table->string('client_name');
            $table->string('mobile', 40);
            $table->string('email');
            $table->string('address', 500)->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('final_budget', 12, 2)->default(0);
            $table->string('status', 20)->default('active');
            $table->string('notes', 1000)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('client_project_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_project_id')->constrained('client_projects')->cascadeOnDelete();
            $table->unsignedSmallInteger('number')->default(1);
            $table->string('label', 120)->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('due_date')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('receipt_name')->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('client_project_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_project_id')->constrained('client_projects')->cascadeOnDelete();
            $table->foreignId('client_project_installment_id')->nullable()->constrained('client_project_installments')->nullOnDelete();
            $table->string('file_path');
            $table->string('file_name')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_project_receipts');
        Schema::dropIfExists('client_project_installments');
        Schema::dropIfExists('client_projects');
    }
};
