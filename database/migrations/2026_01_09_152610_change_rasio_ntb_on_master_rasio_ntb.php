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
        Schema::table('master_rasio_ntb', function (Blueprint $table) {
            $table->decimal('rasio_ntb', 8, 4)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('master_rasio_ntb', function (Blueprint $table) {
            $table->decimal('rasio_ntb', 5, 4)->change();
        });
    }
};
