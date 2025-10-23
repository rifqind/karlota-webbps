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
        Schema::table('master_komoditas', function (Blueprint $table) {
            //
            $table->foreignUuid('edited_by')->after('subsector_id')
                ->nullable()
                ->constrained('users')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_komoditas', function (Blueprint $table) {
            //
            $table->dropForeign(['edited_by']);
            $table->dropColumn(['edited_by', 'created_at', 'updated_at']);
        });
    }
};
