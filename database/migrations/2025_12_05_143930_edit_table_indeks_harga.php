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
        Schema::table('indeks_harga', function (Blueprint $table) {
            $table->dropColumn('indeks_harga');
        });
        Schema::table('indeks_harga', function (Blueprint $table) {
            $table->decimal('indeks_harga', 9, 6)->after('komoditas_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('indeks_harga', function (Blueprint $table) {
            $table->dropColumn('indeks_harga');
        });

        Schema::table('indeks_harga', function (Blueprint $table) {
            $table->decimal('indeks_harga', 3, 3)->after('komoditas_id');
        });
    }
};
