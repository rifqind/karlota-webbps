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
        Schema::table('indeks_harga', function (Blueprint $table) {
            //
            $table->unique(
                ['komoditas_id', 'tahun', 'triwulan'],
                'indeks_harga_komoditas_tahun_triwulan_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('indeks_harga', function (Blueprint $table) {
            //
              $table->dropUnique('indeks_harga_komoditas_tahun_triwulan_unique');
        });
    }
};
