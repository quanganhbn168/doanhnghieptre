<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('trade_posts') || Schema::hasColumn('trade_posts', 'review_note')) {
            return;
        }

        Schema::table('trade_posts', function (Blueprint $table): void {
            $table->text('review_note')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        // Preserve review history on existing deployments.
    }
};
