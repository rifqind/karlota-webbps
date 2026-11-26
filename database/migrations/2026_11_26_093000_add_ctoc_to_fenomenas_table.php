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
        Schema::table('fenomenas', function (Blueprint $table) {
            $table->text('ctoc')->nullable()->after('yony');
        });

        // Pindahkan data implisit pengeluaran tahun >= 2026 ke ctoc, lalu set implisit null
        $pengeluaranSets = DB::table('fenomena_sets')
            ->where('type', 'Pengeluaran')
            ->where('year', '>=', 2026)
            ->pluck('id');

        if ($pengeluaranSets->isNotEmpty()) {
            DB::table('fenomenas')
                ->whereIn('fenomena_sets', $pengeluaranSets)
                ->whereNotNull('implisit')
                ->update([
                    'ctoc' => DB::raw('implisit'),
                    'implisit' => null,
                ]);
        }

        // Tambahkan composite index untuk fenomena_sets agar query cepat
        Schema::table('fenomena_sets', function (Blueprint $table) {
            $table->index(['type', 'year', 'status'], 'fenomenasets_type_year_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan data ctoc ke implisit jika di-rollback
        $pengeluaranSets = DB::table('fenomena_sets')
            ->where('type', 'Pengeluaran')
            ->where('year', '>=', 2026)
            ->pluck('id');

        if ($pengeluaranSets->isNotEmpty()) {
            DB::table('fenomenas')
                ->whereIn('fenomena_sets', $pengeluaranSets)
                ->whereNotNull('ctoc')
                ->update([
                    'implisit' => DB::raw('ctoc'),
                ]);
        }

        Schema::table('fenomena_sets', function (Blueprint $table) {
            $table->dropIndex('fenomenasets_type_year_status_idx');
        });

        Schema::table('fenomenas', function (Blueprint $table) {
            $table->dropColumn('ctoc');
        });
    }
};
