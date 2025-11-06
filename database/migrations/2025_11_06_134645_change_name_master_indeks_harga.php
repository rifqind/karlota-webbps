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
        //
        Schema::rename('master_indeks_harga', 'indeks_harga');
        Schema::table('indeks_harga', function (Blueprint $table) {
            //change name of table to indeks_harga, and add attribute tahun and triwulan (dia integer)
            $table->year('tahun')->after('indeks_harga')->nullable();
            $table->smallInteger('triwulan')->after('tahun')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('indeks_harga', function (Blueprint $table) {
            $table->dropColumn('triwulan');
            $table->dropColumn('tahun');
        });

        Schema::rename('indeks_harga', 'master_indeks_harga');
    }
};
