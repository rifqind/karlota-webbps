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
        Schema::table('row_orders', function (Blueprint $table) {
            //
            $table->string('sekunder_id', 36)->index('rowOrder_idx_sekunder');
            $table->foreign(['sekunder_id'], 'rowOrder_to_sekunder')
                ->references(['id'])
                ->on('sekunder')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('row_orders', function (Blueprint $table) {
            //
            $table->dropForeign('rowOrder_to_sekunder');
            $table->dropColumn('sekunder_id');
        });
    }
};
