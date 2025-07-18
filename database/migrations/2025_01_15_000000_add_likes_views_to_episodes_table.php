<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('episodes', function (Blueprint $table) {
            $table->unsignedBigInteger('likes')->default(0)->after('is_published');
            $table->unsignedBigInteger('views')->default(0)->after('likes');
        });
    }

    public function down(): void
    {
        Schema::table('episodes', function (Blueprint $table) {
            $table->dropColumn(['likes', 'views']);
        });
    }
};