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
        Schema::table('datasets', function (Blueprint $table) {
            $table->foreign(['period_id'], 'datasets_periods')->references(['id'])->on('periods')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['region_id'], 'datasets_regions')->references(['id'])->on('regions')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('datasets', function (Blueprint $table) {
            $table->dropForeign('datasets_periods');
            $table->dropForeign('datasets_regions');
        });
    }
};
