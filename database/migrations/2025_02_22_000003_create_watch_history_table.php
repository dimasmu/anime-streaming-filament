<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watch_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('anime_id')->constrained()->onDelete('cascade');
            $table->foreignId('episode_id')->constrained()->onDelete('cascade');
            $table->integer('current_position')->default(0);
            $table->integer('total_duration')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamp('last_watched_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->integer('watch_time_minutes')->default(0);
            $table->timestamps();

            $table->index('user_id');
            $table->index('anime_id');
            $table->index('episode_id');
            $table->index('last_watched_at');

            $table->unique(['user_id', 'episode_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watch_history');
    }
};
