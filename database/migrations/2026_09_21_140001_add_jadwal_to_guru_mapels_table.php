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
        Schema::table('guru_mapels', function (Blueprint $table) {
            $table->string('jadwal')->nullable()->after('kelas_id'); // Path foto jadwal guru
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guru_mapels', function (Blueprint $table) {
            $table->dropColumn('jadwal');
        });
    }
};
