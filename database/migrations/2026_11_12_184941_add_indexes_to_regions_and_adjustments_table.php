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
        Schema::table('regions', function (Blueprint $table) {
            // Cek apakah index satker_id sudah unique atau belum
            $indexes = Schema::getIndexes('regions');
            $hasSatkerIndex = collect($indexes)->contains('name', 'satker_id');
            $isUnique = collect($indexes)->contains(fn ($idx) => $idx['name'] === 'satker_id' && $idx['unique']);

            if ($hasSatkerIndex && !$isUnique) {
                $table->dropIndex('satker_id');
                $table->unique('satker_id');
            } elseif (!$hasSatkerIndex) {
                $table->unique('satker_id');
            }
        });

        Schema::table('adjustments', function (Blueprint $table) {
            $indexes = Schema::getIndexes('adjustments');
            $hasPdrbIndex = collect($indexes)->contains(fn ($idx) => in_array($idx['name'], ['pdrb_id', 'adjustment_pdrb', 'adjusment_pdrb']));

            if (!$hasPdrbIndex) {
                $table->index('pdrb_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            $indexes = Schema::getIndexes('regions');
            $isUnique = collect($indexes)->contains(fn ($idx) => $idx['name'] === 'satker_id' && $idx['unique']);

            if ($isUnique) {
                $table->dropUnique('regions_satker_id_unique');
                $table->index('satker_id');
            }
        });

        Schema::table('adjustments', function (Blueprint $table) {
            $indexes = Schema::getIndexes('adjustments');
            $hasPdrbIndex = collect($indexes)->contains('name', 'adjustments_pdrb_id_index');

            if ($hasPdrbIndex) {
                $table->dropIndex('adjustments_pdrb_id_index');
            }
        });
    }
};
