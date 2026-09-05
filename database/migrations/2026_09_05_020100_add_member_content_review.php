<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->foreignId('business_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('review_status', 30)->default('approved')->index();
            $table->text('review_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
        });
        Schema::table('businesses', function (Blueprint $table): void {
            $table->json('pending_profile')->nullable();
            $table->text('profile_review_note')->nullable();
            $table->unsignedInteger('profile_revision')->default(0);
        });
    }

    public function down(): void {}
};
