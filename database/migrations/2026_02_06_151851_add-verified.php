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
            $table->boolean('verified')->default(false)->after('description');
        });

        Schema::table('music_tracks', function (Blueprint $table) {
            $table->boolean('verified')->default(false)->after('description');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->boolean('verified')->default(false)->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artworks', function (Blueprint $table) {
            $table->dropColumn('verified');
        });

        Schema::table('music_tracks', function (Blueprint $table) {
            $table->dropColumn('verified');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('verified');
        });
    }
};
