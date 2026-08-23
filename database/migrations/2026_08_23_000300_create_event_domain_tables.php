<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cover_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('type', 50)->default('event')->index();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->string('venue_name')->nullable();
            $table->string('venue_address', 500)->nullable();
            $table->json('location')->nullable();
            $table->timestamp('starts_at')->index();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('registration_opens_at')->nullable();
            $table->timestamp('registration_closes_at')->nullable()->index();
            $table->unsignedInteger('capacity')->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->string('visibility', 30)->default('public')->index();
            $table->json('settings')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('event_registrations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('registered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('full_name');
            $table->string('email')->nullable()->index();
            $table->string('phone', 30)->nullable()->index();
            $table->unsignedSmallInteger('guest_count')->default(0);
            $table->string('status', 30)->default('registered')->index();
            $table->string('attendance_status', 30)->default('pending')->index();
            $table->string('payment_status', 30)->default('not_required')->index();
            $table->json('metadata')->nullable();
            $table->timestamp('registered_at')->useCurrent()->index();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'member_id'], 'event_member_registration_unique');
        });
    }

    public function down(): void
    {
        // Create-only baseline: intentionally no destructive rollback operations.
    }
};
