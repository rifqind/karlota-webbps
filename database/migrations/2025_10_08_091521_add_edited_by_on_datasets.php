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
        Schema::table('datasets', function (Blueprint $table) {
            //
            $table->string('edited_by', 36)->nullable()->index('datasets_idx_users');
            $table->foreign(['edited_by'], 'datasets_to_users')
                ->references(['id'])
                ->on('users')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('datasets', function (Blueprint $table) {
            //
            $table->dropForeign('datasets_to_users');
            $table->dropColumn('edited_by');
        });
    }
};
