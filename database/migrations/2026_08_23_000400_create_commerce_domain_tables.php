<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offering_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('offering_categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('business_offerings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('offering_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cover_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('type', 30)->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->string('price_label')->nullable();
            $table->string('unit')->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();

            $table->fullText(['name', 'summary', 'description'], 'business_offerings_search_fulltext');
        });

        Schema::create('offering_industries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_offering_id')->constrained()->cascadeOnDelete();
            $table->foreignId('industry_id')->constrained()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['business_offering_id', 'industry_id'], 'offering_industry_unique');
        });

        Schema::create('trade_posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('member_id')->constrained()->restrictOnDelete();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cover_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 30)->index();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->string('budget_label')->nullable();
            $table->string('location_label')->nullable()->index();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->string('contact_email')->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('closed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->fullText(['title', 'summary', 'content'], 'trade_posts_search_fulltext');
        });

        Schema::create('trade_post_industries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('trade_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('industry_id')->constrained()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['trade_post_id', 'industry_id']);
        });
    }

    public function down(): void
    {
        // Create-only baseline: intentionally no destructive rollback operations.
    }
};
