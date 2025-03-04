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
        Schema::table('pdrbs', function (Blueprint $table) {
            $table->foreign(['dataset_id'], 'pdrb_datasets')->references(['id'])->on('datasets')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['subsector_id'], 'pdrb_subsector')->references(['id'])->on('subsectors')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pdrbs', function (Blueprint $table) {
            $table->dropForeign('pdrb_datasets');
            $table->dropForeign('pdrb_subsector');
        });
    }
};
