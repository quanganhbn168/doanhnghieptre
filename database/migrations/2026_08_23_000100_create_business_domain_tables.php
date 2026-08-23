<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('business_categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('businesses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('slug')->unique();
            $table->string('tax_code', 32)->nullable()->unique();
            $table->string('business_type', 50)->nullable()->index();
            $table->string('phone', 30)->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->string('website')->nullable();
            $table->string('address', 500)->nullable();
            $table->string('province', 100)->nullable()->index();
            $table->string('district', 100)->nullable()->index();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->json('social_links')->nullable();
            $table->json('location')->nullable();
            $table->foreignId('cover_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('status', 30)->default('draft')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->fullText(['name', 'legal_name', 'summary', 'description'], 'businesses_search_fulltext');
        });

        Schema::create('business_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('role', 50)->default('representative')->index();
            $table->string('job_title')->nullable();
            $table->boolean('is_primary')->default(false)->index();
            $table->string('status', 30)->default('active')->index();
            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'member_id']);
        });

        Schema::create('business_industries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('industry_id')->constrained()->restrictOnDelete();
            $table->boolean('is_primary')->default(false)->index();
            $table->timestamps();

            $table->unique(['business_id', 'industry_id']);
        });

        Schema::create('business_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30)->index();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        // Create-only baseline: intentionally no destructive rollback operations.
    }
};
