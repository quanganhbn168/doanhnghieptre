<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('business_chapters')) {
            Schema::create('business_chapters', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('businesses') && ! Schema::hasColumn('businesses', 'business_chapter_id')) {
            Schema::table('businesses', function (Blueprint $table): void {
                $table->foreignId('business_chapter_id')->nullable()->after('business_category_id')->constrained('business_chapters')->nullOnDelete();
                $table->string('business_size', 30)->nullable()->after('business_type')->index();
            });
        }
    }

    public function down(): void
    {
        // Dữ liệu danh bạ cần được bảo toàn; không rollback phá huỷ.
    }
};
