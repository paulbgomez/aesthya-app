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
            $table->integer('artistic_period_id')->after('id');
            $table->json('color_ids')->after('artistic_period_id');
            $table->integer('poem_id')->after('color_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('moodboards', function (Blueprint $table) {
            $table->dropColumn('artistic_period_id');
            $table->dropColumn('color_ids');
            $table->dropColumn('poem_id');
        });
    }
};
