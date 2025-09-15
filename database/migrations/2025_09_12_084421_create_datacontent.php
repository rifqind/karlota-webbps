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
        Schema::create('variabel', function (Blueprint $table) {
            $table->id();
            $table->string('label', 50)->default('Kelompok Variabel');
            $table->string('sekunder_id', 36)->index('variabel_idx_sekunder');
            $table->foreign(['sekunder_id'], 'sekunder_to_variabel')
                ->references(['id'])
                ->on('sekunder')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
        Schema::create('status_sekunder', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('sekunder_id', 36)->index('status_idx_sekunder');
            $table->year('tahun');
            $table->tinyInteger('status')->default(1);
            $table->string('created_by', 36)->index('status_idx_users');
            $table->timestamps();
            $table->foreign(['sekunder_id'], 'status_to_sekunder')
                ->references(['id'])
                ->on('sekunder')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->foreign(['created_by'], 'status_to_user')
                ->references(['id'])
                ->on('users')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
        Schema::create('datacontent', function (Blueprint $table) {
            $table->id();
            $table->decimal('data', 19, 9)->nullable();
            $table->foreignUuid('status_id')->index('datacontent_idx_status');
            $table->unsignedBigInteger('row_id')->index('datacontent_idx_row');
            // $table->unsignedBigInteger('variabel_id')->index('datacontent_idx_variabel');
            $table->tinyInteger('triwulan');
            $table->foreign(['row_id'], 'datacontent_to_rows')
                ->references(['id'])
                ->on('rows')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->foreign(['status_id'], 'datacontent_to_status')
                ->references(['id'])
                ->on('status_sekunder')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            // $table->foreign(['variabel_id'], 'datacontent_to_variabel')
            //     ->references(['id'])
            //     ->on('variabel')
            //     ->onUpdate('restrict')
            //     ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('datacontent');
        Schema::dropIfExists('variabel');
        Schema::dropIfExists('status_sekunder');
    }
};
