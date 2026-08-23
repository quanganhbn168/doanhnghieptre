<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intros', function (Blueprint $table): void {
            $table->string('type', 40)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        // Không ép lại loại nội dung cũ để không làm mất tính linh hoạt của Intro.
    }
};
