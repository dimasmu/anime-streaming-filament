<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('anime_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('episode_id')->nullable()->constrained()->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->integer('timestamp')->nullable()->comment('Bookmark position in seconds');
            $table->timestamps();

            $table->unique(['user_id', 'anime_id', 'episode_id'], 'unique_user_bookmark');
            $table->index(['user_id', 'anime_id']);
            $table->index(['user_id', 'episode_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookmarks');
    }
};
