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
        Schema::table('summary_time', function (Blueprint $table) {
            $table->foreign(['period_id'], 'sumtime_to_period')->references(['id'])->on('periods')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['id_user'], 'sumtime_to_user')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('summary_time', function (Blueprint $table) {
            $table->dropForeign('sumtime_to_period');
            $table->dropForeign('sumtime_to_user');
        });
    }
};
