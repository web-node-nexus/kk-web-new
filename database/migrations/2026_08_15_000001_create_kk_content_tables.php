<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 191)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 191)->unique();
            $table->string('number', 10);
            $table->string('title');
            $table->string('description');
            $table->text('long_description')->nullable();
            $table->json('features')->nullable();
            $table->string('color')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('industries', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 191)->unique();
            $table->string('title');
            $table->text('description');
            $table->string('color')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        Schema::create('case_studies', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 191)->unique();
            $table->string('title');
            $table->string('industry');
            $table->string('result')->nullable();
            $table->text('summary');
            $table->string('cover_image')->nullable();
            $table->string('project_url')->nullable();
            $table->string('video')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(true);
            $table->timestamps();
        });

        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug', 191)->unique();
            $table->timestamps();
        });

        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug', 191)->unique();
            $table->string('title');
            $table->text('excerpt');
            $table->longText('body')->nullable();
            $table->string('cover_image')->nullable();
            $table->unsignedInteger('read_time_mins')->default(5);
            $table->boolean('is_popular')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('job_openings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('employment_type');
            $table->string('location');
            $table->text('description')->nullable();
            $table->boolean('is_open')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role');
            $table->text('bio')->nullable();
            $table->string('phone')->nullable();
            $table->string('group')->default('team');
            $table->string('photo')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('contact_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('service')->nullable();
            $table->text('message');
            $table->boolean('agreed_terms')->default(false);
            $table->string('status')->default('new');
            $table->timestamps();
        });

        Schema::create('project_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('service')->nullable();
            $table->string('budget_range')->nullable();
            $table->string('timeline')->nullable();
            $table->text('description');
            $table->string('attachment_path')->nullable();
            $table->string('status')->default('new');
            $table->timestamps();
        });

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email', 191)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('project_requests');
        Schema::dropIfExists('contact_inquiries');
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('job_openings');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_categories');
        Schema::dropIfExists('case_studies');
        Schema::dropIfExists('industries');
        Schema::dropIfExists('services');
        Schema::dropIfExists('site_settings');
    }
};
