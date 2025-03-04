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
        Schema::table('adjustments', function (Blueprint $table) {
            $table->foreign(['pdrb_id'], 'adjustment_pdrb')->references(['id'])->on('pdrbs')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adjustments', function (Blueprint $table) {
            $table->dropForeign('adjustment_pdrb');
        });
    }
};
