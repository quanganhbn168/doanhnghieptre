<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table): void {
            $table->string('membership_code', 40)->nullable()->unique();
            $table->string('representative_name')->nullable();
            $table->timestamp('association_approved_at')->nullable();
            $table->foreignId('association_approved_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::create('business_chapter_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_chapter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['business_chapter_id', 'user_id']);
        });

        // Existing approved businesses retain their membership; do not replay approval.
        DB::table('businesses')->where('status', 'approved')->orderBy('id')->each(function ($business): void {
            DB::table('businesses')->where('id', $business->id)->update([
                'membership_code' => 'DNTBN-DN-'.str_pad((string) $business->id, 6, '0', STR_PAD_LEFT),
            ]);
        });
    }

    public function down(): void
    {
        // Preserve membership codes, review history and assignments on rollback.
    }
};
