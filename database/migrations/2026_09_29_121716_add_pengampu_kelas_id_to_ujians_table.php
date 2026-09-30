<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     */
    public function up(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $table->foreignId('pengampu_kelas_id')
                ->nullable()
                ->after('durasi_menit')
                ->constrained('pengampu_kelas')
                ->cascadeOnDelete();
        });
    }

    /**
     * Batalkan migration.
     */
    public function down(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $table->dropForeign(['pengampu_kelas_id']);
            $table->dropColumn('pengampu_kelas_id');
        });
    }
};
