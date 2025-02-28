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
        Schema::table('fenomenas', function (Blueprint $table) {
            $table->foreign(['category_id'], 'fenomena_categories')->references(['id'])->on('categories')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['sector_id'], 'fenomena_sector')->references(['id'])->on('sectors')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['subsector_id'], 'fenomena_subsector')->references(['id'])->on('subsectors')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['fenomena_sets'], 'fenomena_to_sets')->references(['id'])->on('fenomena_sets')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fenomenas', function (Blueprint $table) {
            $table->dropForeign('fenomena_categories');
            $table->dropForeign('fenomena_sector');
            $table->dropForeign('fenomena_subsector');
            $table->dropForeign('fenomena_to_sets');
        });
    }
};
