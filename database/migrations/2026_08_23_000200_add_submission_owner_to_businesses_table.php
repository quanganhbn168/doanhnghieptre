<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table): void {
            $table->foreignId('submitted_by_user_id')
                ->nullable()
                ->after('approved_by')
                ->constrained('users')
                ->nullOnDelete()
                ->index();
        });
    }

    public function down(): void
    {
        // Deliberately left non-destructive to preserve submitted applications.
    }
};
