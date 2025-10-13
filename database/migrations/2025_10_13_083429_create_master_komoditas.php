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
        Schema::create('master_komoditas', function (Blueprint $table) {
            $table->id();
            $table->string('label')->nullable();
            $table->string('code')->default('default_code');
            $table->char('type', 1)->default('1');
            $table->string('satuan', 14)->default('1');
            $table->unsignedBigInteger('category_id')->index('master_komoditas_idx_category')->nullable();
            $table->foreign(['category_id'], 'master_komoditas_to_category')
                ->references(['id'])
                ->on('categories')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->unsignedBigInteger('sector_id')->index('master_komoditas_idx_sector')->nullable();
            $table->foreign(['sector_id'], 'master_komoditas_to_sector')
                ->references(['id'])
                ->on('sectors')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->unsignedBigInteger('subsector_id')->index('master_komoditas_idx_subsector')->nullable();
            $table->foreign(['subsector_id'], 'master_komoditas_to_subsector')
                ->references(['id'])
                ->on('subsectors')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });

        Schema::create('master_harga', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('komoditas_id')->index('master_harga_idx_komoditas')->nullable();
            $table->foreign(['komoditas_id'], 'master_harga_to_komoditas')
                ->references(['id'])
                ->on('master_komoditas')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->decimal('harga_konstan', 8, 4)->default(0);
        });

        Schema::create('master_indeks_harga', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('komoditas_id')->index('master_indeks_harga_idx_komoditas')->nullable();
            $table->foreign(['komoditas_id'], 'master_indeks_harga_to_komoditas')
                ->references(['id'])
                ->on('master_komoditas')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->decimal('indeks_harga', 3, 3)->default(0);
        });

        Schema::create('master_sut_irio', function (Blueprint $table) {
            $table->id();
            $table->string('label')->default('irio_default');
        });

        Schema::create('master_rasio_ntb', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('komoditas_id')->index('master_rasio_ntb_idx_komoditas')->nullable();
            $table->foreign(['komoditas_id'], 'master_rasio_ntb_to_komoditas')
                ->references(['id'])
                ->on('master_komoditas')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->unsignedBigInteger('sut_id')->index('master_rasio_ntb_idx_sut')->nullable();
            $table->foreign(['sut_id'], 'master_rasio_ntb_to_sut')
                ->references(['id'])
                ->on('master_sut_irio')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->decimal('rasio_ntb', 5, 4)->default(0);
        });

        Schema::create('lk_datadasar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('komoditas_id')->index('lk_datadasar_idx_komoditas')->nullable();
            $table->foreign(['komoditas_id'], 'lk_datadasar_to_komoditas')
                ->references(['id'])
                ->on('master_komoditas')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->decimal('produksi', 15, 4)->default(0);
            $table->year('tahun')->default(2019);
            $table->smallInteger('triwulan')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
     public function down(): void
    {
        // Hapus tabel dengan foreign keys yang merujuk ke tabel lain terlebih dahulu.
        Schema::dropIfExists('lk_datadasar');
        Schema::dropIfExists('master_rasio_ntb');
        Schema::dropIfExists('master_indeks_harga');
        Schema::dropIfExists('master_harga');
        
        // Hapus tabel induk.
        Schema::dropIfExists('master_komoditas');
        Schema::dropIfExists('master_sut_irio');
    }
};
