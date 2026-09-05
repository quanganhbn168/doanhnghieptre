<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('industries', function (Blueprint $table): void {
            $table->uuid('image_id')->nullable();
            $table->foreign('image_id')->references('id')->on('curator')->nullOnDelete();
            $table->boolean('show_on_home')->default(true);
            $table->string('source_url', 512)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('industries', function (Blueprint $table): void {
            $table->dropForeign(['image_id']);
            $table->dropColumn(['image_id', 'show_on_home', 'source_url']);
        });
    }
};
