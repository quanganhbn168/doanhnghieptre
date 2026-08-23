<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trade_posts', function (Blueprint $table): void {
            $table->text('review_note')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        // Deliberately non-destructive for review history.
    }
};
