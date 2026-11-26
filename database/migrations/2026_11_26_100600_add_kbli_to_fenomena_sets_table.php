<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fenomena_sets', function (Blueprint $table) {
            $table->string('kbli')->default('kbli_2020')->after('id');
        });

        // Set semua fenomena_sets yang sudah ada ke 'kbli_2020'
        DB::table('fenomena_sets')
            ->whereNull('kbli')
            ->orWhere('kbli', '')
            ->update(['kbli' => 'kbli_2020']);

        Schema::table('fenomena_sets', function (Blueprint $table) {
            $table->index(['kbli', 'type', 'year', 'status'], 'fenomenasets_kbli_type_year_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fenomena_sets', function (Blueprint $table) {
            $table->dropIndex('fenomenasets_kbli_type_year_status_idx');
            $table->dropColumn('kbli');
        });
    }
};
