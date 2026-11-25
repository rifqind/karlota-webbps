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
        if (!Schema::hasColumn('summary_pdrb', 'kbli')) {
            Schema::table('summary_pdrb', function (Blueprint $table) {
                $table->string('kbli', 20)->default('kbli_2020')->after('id')->index();
            });
        }

        if (!Schema::hasColumn('summary_time', 'kbli')) {
            Schema::table('summary_time', function (Blueprint $table) {
                $table->string('kbli', 20)->default('kbli_2020')->after('type')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('summary_pdrb', 'kbli')) {
            Schema::table('summary_pdrb', function (Blueprint $table) {
                $table->dropColumn('kbli');
            });
        }

        if (Schema::hasColumn('summary_time', 'kbli')) {
            Schema::table('summary_time', function (Blueprint $table) {
                $table->dropColumn('kbli');
            });
        }
    }
};
