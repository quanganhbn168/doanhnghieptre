<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Ordinary applicants from the old flow can now sign in to submit their
        // business application. Staff and explicitly rejected accounts are untouched.
        User::query()->where('approval_status', 'pending')
            ->whereDoesntHave('roles')->whereDoesntHave('permissions')
            ->update(['approval_status' => 'approved', 'is_active' => true]);
    }

    public function down(): void
    {
        // Removing an obsolete gate must not revoke existing users' access.
    }
};
