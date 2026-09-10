<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('project_group_messages')) {
            Schema::create('project_group_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('client_project_id');
                $table->unsignedBigInteger('employee_id');
                $table->text('message');
                $table->json('mentions')->nullable();
                $table->timestamps();
                $table->index(['client_project_id', 'id']);
                $table->index('employee_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('project_group_messages');
    }
};
