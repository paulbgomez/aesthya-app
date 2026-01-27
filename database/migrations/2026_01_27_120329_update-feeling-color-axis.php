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
        Schema::table('feelings', function (Blueprint $table) {
            $table->enum('color', ['red', 'blue', 'green', 'yellow'])->default('blue')->after('description');
            $table->enum('energy_axis', ['high', 'low'])->default('high')->after('color');
            $table->enum('pleasantness_axis', ['pleasant', 'unpleasant'])->default('pleasant')->after('energy_axis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feelings', function (Blueprint $table) {
            $table->dropColumn('color');
            $table->dropColumn('energy_axis');
            $table->dropColumn('pleasantness_axis');
        });
    }
};
