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
        Schema::create('pdrbs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dataset_id')->index('pdrb_datasets');
            $table->year('year');
            $table->char('quarter', 1);
            $table->unsignedBigInteger('subsector_id')->index('pdrb_subsector');
            $table->decimal('adhb', 19, 9)->nullable();
            $table->decimal('adhk', 19, 9)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdrbs');
    }
};
