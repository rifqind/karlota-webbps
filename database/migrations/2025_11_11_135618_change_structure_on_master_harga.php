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
        Schema::table('master_harga', function (Blueprint $table) {
            $table->dropColumn('harga_konstan');
            $table->decimal('harga_konstan', 19,9)->after('komoditas_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
