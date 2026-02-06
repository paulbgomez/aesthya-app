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
        Schema::table('artworks', function (Blueprint $table) {
            // Change image_url back to text for long Wikipedia URLs
            $table->text('image_url')->nullable()->change();
            
            // Add unique constraint to prevent duplicates
            $table->unique(['title', 'artist'], 'artworks_title_artist_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artworks', function (Blueprint $table) {
            $table->string('image_url')->nullable()->change();
            $table->dropUnique('artworks_title_artist_unique');
        });
    }
};
