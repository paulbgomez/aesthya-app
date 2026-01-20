<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('music_tracks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('artist');
            $table->text('preview_url')->nullable();
            $table->text('album_art')->nullable();
            $table->json('genres')->nullable();
            $table->jsonb('mood_tags')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index('mood_tags', null, 'gin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('music_tracks');
    }
};