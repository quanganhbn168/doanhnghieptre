<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table): void {
            $table->string('representative_job_title', 160)
                ->nullable()
                ->after('submitted_by_user_id');
        });
    }

    public function down(): void
    {
        // Deliberately non-destructive for submitted business records.
    }
};
