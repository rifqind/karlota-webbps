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
        Schema::create('summary_time', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('type', 30)->nullable();
            $table->unsignedBigInteger('period_id')->index('sumtime_to_period');
            $table->string('id_user', 36)->default('9e5d7684-c6e2-4b9d-a4f5-da8f412a69c9')->index('sumtime_to_user');
            $table->timestamp('timestamp')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('summary_time');
    }
};
