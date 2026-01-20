<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('artwork_id')->constrained()->onDelete('cascade');
            $table->foreignId('moodboard_id')->nullable()->constrained()->onDelete('set null');
            $table->string('feeling');
            $table->json('context')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'artwork_id', 'moodboard_id']);
            
            $table->index('user_id');
            $table->index('feeling');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_favorites');
    }
};