<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_opening_id')->nullable()->constrained()->nullOnDelete();
            $table->string('position');
            $table->string('department')->nullable();
            $table->string('job_type')->nullable();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('nationality')->nullable();
            $table->text('address')->nullable();
            $table->string('qualification')->nullable();
            $table->string('university')->nullable();
            $table->string('passing_year')->nullable();
            $table->string('field_of_study')->nullable();
            $table->string('percentage_cgpa')->nullable();
            $table->string('total_experience')->nullable();
            $table->string('last_company')->nullable();
            $table->string('job_title')->nullable();
            $table->text('responsibilities')->nullable();
            $table->string('resume_path')->nullable();
            $table->string('cover_letter_path')->nullable();
            $table->text('why_join')->nullable();
            $table->string('source')->nullable();
            $table->boolean('declared')->default(false);
            $table->string('status')->default('new');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
