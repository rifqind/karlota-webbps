<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('kbli')->after('id')->default('kbli_2020');
        });

        Schema::table('sectors', function (Blueprint $table) {
            $table->string('kbli')->after('id')->default('kbli_2020');
        });

        Schema::table('subsectors', function (Blueprint $table) {
            $table->string('kbli')->after('id')->default('kbli_2020');
        });

        // Memastikan seluruh data yang sudah ada terisi nilai 'kbli_2020'
        DB::table('categories')->whereNull('kbli')->orWhere('kbli', '')->update(['kbli' => 'kbli_2020']);
        DB::table('sectors')->whereNull('kbli')->orWhere('kbli', '')->update(['kbli' => 'kbli_2020']);
        DB::table('subsectors')->whereNull('kbli')->orWhere('kbli', '')->update(['kbli' => 'kbli_2020']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subsectors', function (Blueprint $table) {
            $table->dropColumn('kbli');
        });

        Schema::table('sectors', function (Blueprint $table) {
            $table->dropColumn('kbli');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('kbli');
        });
    }
};
