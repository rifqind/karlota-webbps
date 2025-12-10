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
        Schema::table('lk_datadasar', function (Blueprint $table) {
            //
            $table->timestamps();
            $table->unique(
                ['komoditas_id', 'tahun', 'triwulan'],
                'lk_datadasar_komoditas_tahun_triwulan_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lk_datadasar', function (Blueprint $table) {
            //
            $table->dropColumn(['created_at', 'updated_at']);
            $table->dropUnique('lk_datadasar_komoditas_tahun_triwulan_unique');
        });
    }
};
