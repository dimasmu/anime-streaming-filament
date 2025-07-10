<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('animes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('synopsis')->nullable();
            $table->string('poster_image')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('trailer_url')->nullable();
            $table->enum('status', ['ongoing', 'completed', 'upcoming', 'hiatus'])->default('upcoming');
            $table->enum('type', ['tv', 'movie', 'ova', 'ona', 'special'])->default('tv');
            $table->integer('episodes_count')->nullable();
            $table->integer('duration')->nullable(); // in minutes
            $table->date('release_date')->nullable();
            $table->decimal('rating', 3, 1)->nullable();
            $table->foreignId('studio_id')->nullable()->constrained()->onDelete('set null');
            $table->string('source')->nullable(); // manga, novel, original, etc.
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animes');
    }
};
