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
        Schema::create('moodboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('journal_id');
            $table->string('feeling');
            $table->json('artwork_ids')->nullable();
            $table->json('music_ids')->nullable();
            $table->json('video_ids')->nullable();
            $table->json('book_ids')->nullable();
            $table->json('generation_context')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('feeling');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moodboards');
    }
};
