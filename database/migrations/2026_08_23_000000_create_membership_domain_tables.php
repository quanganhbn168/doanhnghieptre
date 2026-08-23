<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('industries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('industries')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('organization_positions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('group_name')->nullable();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('member_code')->unique();
            $table->string('full_name');
            $table->string('email')->nullable()->index();
            $table->string('phone', 30)->nullable()->index();
            $table->foreignId('avatar_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('province', 100)->nullable()->index();
            $table->string('district', 100)->nullable()->index();
            $table->text('introduction')->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->timestamp('joined_at')->nullable()->index();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('member_applications', function (Blueprint $table): void {
            $table->id();
            $table->string('application_code')->unique();
            $table->foreignId('submitted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('full_name');
            $table->string('email')->nullable()->index();
            $table->string('phone', 30)->nullable()->index();
            $table->string('membership_type', 50)->default('official')->index();
            $table->string('business_name')->nullable();
            $table->string('tax_code', 32)->nullable()->index();
            $table->json('submitted_data')->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->text('review_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable()->index();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('member_application_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('member_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('document_type', 50)->index();
            $table->string('status', 30)->default('submitted')->index();
            $table->text('review_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['member_application_id', 'media_id'], 'application_document_media_unique');
        });

        Schema::create('member_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30)->index();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at')->useCurrent()->index();
        });

        Schema::create('member_position_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_position_id')->constrained()->restrictOnDelete();
            $table->string('appointment_reference')->nullable();
            $table->date('appointed_at')->nullable()->index();
            $table->date('ended_at')->nullable()->index();
            $table->boolean('is_current')->default(true)->index();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['member_id', 'organization_position_id'], 'member_position_assignment_index');
        });
    }

    public function down(): void
    {
        // Create-only baseline: intentionally no destructive rollback operations.
    }
};
