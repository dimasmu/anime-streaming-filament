<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, make the column nullable and allow any string temporarily
        Schema::table('episodes', function (Blueprint $table) {
            $table->string('quality')->nullable()->change();
        });

        // Update existing data to new format
        DB::statement("UPDATE episodes SET quality = '720' WHERE quality = 'HD'");
        DB::statement("UPDATE episodes SET quality = '480' WHERE quality = 'SD'");
        DB::statement("UPDATE episodes SET quality = '1080' WHERE quality = '4K'");

        // Now modify the column to new enum values
        DB::statement("ALTER TABLE episodes MODIFY COLUMN quality ENUM('360', '480', '720', '1080') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, make the column nullable and allow any string temporarily
        Schema::table('episodes', function (Blueprint $table) {
            $table->string('quality')->nullable()->change();
        });

        // Revert data back to old format
        DB::statement("UPDATE episodes SET quality = 'HD' WHERE quality = '720'");
        DB::statement("UPDATE episodes SET quality = 'SD' WHERE quality = '480'");
        DB::statement("UPDATE episodes SET quality = '4K' WHERE quality = '1080'");
        DB::statement("UPDATE episodes SET quality = NULL WHERE quality = '360'");

        // Revert the column to old enum values
        DB::statement("ALTER TABLE episodes MODIFY COLUMN quality ENUM('HD', 'SD', '4K') NULL");
    }
};
