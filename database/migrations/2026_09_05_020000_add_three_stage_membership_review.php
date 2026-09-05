<?php

use Database\Seeders\AssociationRoleSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table): void {
            $table->string('application_code', 40)->nullable()->unique();
            $table->string('application_email')->nullable()->index();
            $table->timestamp('chapter_reviewed_at')->nullable();
            $table->foreignId('chapter_reviewed_by')->nullable()->constrained('users')->nullOnDelete();
        });
        // Previous "rejected" meant a request for corrections, never final refusal.
        DB::table('businesses')->where('status', 'rejected')->update(['status' => 'changes_requested']);
        foreach (['from_status', 'to_status'] as $column) {
            DB::table('business_status_histories')->where($column, 'rejected')->update([$column => 'changes_requested']);
        }
        DB::table('businesses')->orderBy('id')->each(function ($business): void {
            DB::table('businesses')->where('id', $business->id)->update([
                'application_code' => 'HS-DNT-'.str_pad((string) $business->id, 8, '0', STR_PAD_LEFT),
                'application_email' => $business->submitted_by_user_id
                    ? DB::table('users')->where('id', $business->submitted_by_user_id)->value('email') : $business->email,
            ]);
        });
        // Retain approved memberships, existing users, chapter assignments and files.
        (new AssociationRoleSeeder)->run();
    }

    public function down(): void
    {
        // Approval history and issued memberships must not be discarded on rollback.
    }
};
