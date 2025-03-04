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
        Schema::table('fenomena_sets', function (Blueprint $table) {
            $table->foreign(['region_id'], 'fenomenasets_region')->references(['id'])->on('regions')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fenomena_sets', function (Blueprint $table) {
            $table->dropForeign('fenomenasets_region');
        });
    }
};
