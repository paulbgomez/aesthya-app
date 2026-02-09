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
        Schema::table('moodboards', function (Blueprint $table) {
            $table->integer('artistic_period_id')->nullable()->change();
            $table->json('color_ids')->nullable()->change();
            $table->integer('poem_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('moodboards', function (Blueprint $table) {
            $table->integer('artistic_period_id')->nullable(false)->change();
            $table->json('color_ids')->nullable(false)->change();
            $table->integer('poem_id')->nullable(false)->change();
        });
    }
};
