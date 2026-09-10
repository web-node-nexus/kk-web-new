<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('personal_todos')) {
            Schema::create('personal_todos', function (Blueprint $table) {
                $table->id();
                $table->string('owner_type', 20); // admin | employee
                $table->unsignedBigInteger('owner_id');
                $table->string('title');
                $table->text('notes')->nullable();
                $table->string('priority', 20)->default('medium'); // low|medium|high
                $table->date('due_date')->nullable();
                $table->boolean('is_done')->default(false);
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                $table->index(['owner_type', 'owner_id']);
                $table->index(['owner_type', 'owner_id', 'is_done']);
            });
        }

        if (! Schema::hasTable('client_project_renewals')) {
            Schema::create('client_project_renewals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('client_project_id');
                $table->string('type', 40); // domain|server|consulting|maintenance|hosting|ssl|other
                $table->string('name');
                $table->decimal('amount', 12, 2)->nullable();
                $table->date('renew_date')->nullable();
                $table->string('status', 20)->default('upcoming'); // upcoming|due|paid|cancelled
                $table->string('vendor')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index('client_project_id');
                $table->index(['client_project_id', 'renew_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('client_project_renewals');
        Schema::dropIfExists('personal_todos');
    }
};
