<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('approval_status', 30)->default('approved')->index()->after('is_active');
            $table->foreignId('account_approved_by')->nullable()->after('approval_status')->constrained('users')->nullOnDelete();
            $table->timestamp('account_approved_at')->nullable()->after('account_approved_by');
        });
    }
};
