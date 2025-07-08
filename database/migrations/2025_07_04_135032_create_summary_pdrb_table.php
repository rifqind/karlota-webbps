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
        Schema::create('summary_pdrb', function (Blueprint $table) {
            $table->integer('id', true);
            $table->bigInteger('category_id')->nullable();
            $table->bigInteger('sector_id')->nullable();
            $table->bigInteger('subsector_id')->nullable();
            $table->bigInteger('region_id');
            $table->tinyInteger('quarter');
            $table->decimal('adhb', 19, 9)->nullable();
            $table->decimal('adhk', 19, 9)->nullable();
            $table->decimal('dist', 11, 6)->nullable();
            $table->decimal('qtoq', 11, 6)->nullable();
            $table->decimal('yony', 11, 6)->nullable();
            $table->decimal('ctoc', 11, 6)->nullable();
            $table->decimal('idx', 11, 6)->nullable();
            $table->decimal('iqtoq', 11, 6)->nullable();
            $table->decimal('iyony', 11, 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('summary_pdrb');
    }
};
