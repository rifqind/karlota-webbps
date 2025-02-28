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
        Schema::create('fenomenas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('fenomena_sets')->index('fenomena_to_sets');
            $table->unsignedBigInteger('category_id')->index('fenomena_categories');
            $table->unsignedBigInteger('sector_id')->index('fenomena_sector');
            $table->unsignedBigInteger('subsector_id')->index('fenomena_subsector');
            $table->text('fenomena_qtoq');
            $table->text('fenomena_yony');
            $table->text('fenomena_implisit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fenomenas');
    }
};
