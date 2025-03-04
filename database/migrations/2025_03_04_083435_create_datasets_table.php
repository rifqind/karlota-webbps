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
        Schema::create('datasets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('type', 25)->nullable();
            $table->unsignedBigInteger('period_id')->index('datasets_periods');
            $table->unsignedBigInteger('region_id')->index('datasets_regions');
            $table->year('year');
            $table->char('quarter', 1);
            $table->char('status', 10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('datasets');
    }
};
