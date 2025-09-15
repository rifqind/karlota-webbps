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
        Schema::table('produsen', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('region_id')->index('produsen_idx_region')->after('nama');
            $table->foreign(['region_id'], 'produsen_to_region')
                ->references(['id'])
                ->on('regions')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produsen', function (Blueprint $table) {
            //
            $table->dropForeign('produsen_to_region');
            $table->dropColumn('region_id');
        });
    }
};
