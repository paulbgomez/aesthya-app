<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('content')->nullable();
            $table->date('creation_date')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('creation_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal');
    }
};
