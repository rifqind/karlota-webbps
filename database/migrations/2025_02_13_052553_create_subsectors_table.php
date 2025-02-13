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
        Schema::create('subsectors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('type', 25);
            $table->unsignedBigInteger('sector_id')->index('subsector_sector');
            $table->string('code', 3)->nullable();
            $table->string('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subsectors');
    }
};
