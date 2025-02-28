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
        Schema::create('fenomena_sets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type', 20);
            $table->unsignedBigInteger('region_id')->index('fenomenasets_region');
            $table->smallInteger('year');
            $table->boolean('quarter');
            $table->string('status', 10);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fenomena_sets');
    }
};
