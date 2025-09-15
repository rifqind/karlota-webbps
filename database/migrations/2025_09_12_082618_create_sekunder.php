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
        Schema::create('sekunder', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('label', 100)->default('Judul Data');
            $table->unsignedBigInteger('produsen_id')->index('sekunder_idx_produsen');
            $table->timestamps();
            $table->string('created_by', 36)->index('sekunder_idx_users');
            $table->foreign(['produsen_id'], 'sekunder_to_produsen')
                ->references(['id'])
                ->on('produsen')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->foreign(['created_by'], 'sekunder_to_user')
                ->references(['id'])
                ->on('users')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('sekunder');
    }
};
