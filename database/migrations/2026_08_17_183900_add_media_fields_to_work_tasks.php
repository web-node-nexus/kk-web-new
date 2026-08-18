<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_tasks', function (Blueprint $table) {
            $table->string('pdf_path')->nullable();
            $table->string('pdf_name')->nullable();
            $table->string('video_path')->nullable();
            $table->string('video_name')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('link_url', 500)->nullable();
            $table->string('meet_link', 500)->nullable();
        });

        Schema::table('work_task_replies', function (Blueprint $table) {
            $table->string('pdf_path')->nullable();
            $table->string('pdf_name')->nullable();
            $table->string('video_path')->nullable();
            $table->string('video_name')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('link_url', 500)->nullable();
            $table->string('meet_link', 500)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('work_tasks', function (Blueprint $table) {
            $table->dropColumn([
                'pdf_path', 'pdf_name', 'video_path', 'video_name',
                'file_path', 'file_name', 'link_url', 'meet_link',
            ]);
        });

        Schema::table('work_task_replies', function (Blueprint $table) {
            $table->dropColumn([
                'pdf_path', 'pdf_name', 'video_path', 'video_name',
                'file_path', 'file_name', 'link_url', 'meet_link',
            ]);
        });
    }
};
