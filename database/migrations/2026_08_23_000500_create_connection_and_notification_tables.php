<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_connections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('initiator_member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('recipient_member_id')->constrained('members')->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['initiator_member_id', 'recipient_member_id'], 'member_connection_unique');
        });

        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Create-only baseline: intentionally no destructive rollback operations.
    }
};
