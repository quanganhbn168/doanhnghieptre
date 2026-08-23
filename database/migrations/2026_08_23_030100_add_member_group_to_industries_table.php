<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('industries', 'is_member_group')) {
            Schema::table('industries', function (Blueprint $table): void {
                $table->boolean('is_member_group')->default(false)->after('is_active')->index();
            });
        }
    }

    public function down(): void
    {
        // Danh mục đã được sử dụng trong hồ sơ nên không rollback phá huỷ.
    }
};
