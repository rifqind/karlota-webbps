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
        Schema::create('periods', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('type', 25)->nullable();
            $table->year('year');
            $table->char('quarter', 1);
            $table->string('description');
            $table->char('status', 25)->default('Aktif');
            $table->date('started_at');
            $table->date('ended_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periods');
    }
};
