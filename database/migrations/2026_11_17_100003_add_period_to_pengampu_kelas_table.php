<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengampu_kelas', function (Blueprint $table) {
            if (!Schema::hasColumn('pengampu_kelas', 'tahun_ajaran')) {
                $table->string('tahun_ajaran', 20)->default('2026/2027')->after('kelas_id');
            }
            if (!Schema::hasColumn('pengampu_kelas', 'semester')) {
                $table->string('semester', 10)->default('ganjil')->after('tahun_ajaran');
            }
        });

        // Pastikan index FK tersedia sebelum menghapus unique index lama
        Schema::table('pengampu_kelas', function (Blueprint $table) {
            $table->index('guru_mapel_id', 'pengampu_kelas_guru_mapel_idx');
            $table->index('kelas_id', 'pengampu_kelas_kelas_idx');
        });

        Schema::table('pengampu_kelas', function (Blueprint $table) {
            $table->dropUnique('pengampu_kelas_guru_mapel_id_kelas_id_unique');
        });

        Schema::table('pengampu_kelas', function (Blueprint $table) {
            $table->unique(
                ['guru_mapel_id', 'kelas_id', 'tahun_ajaran', 'semester'],
                'pengampu_kelas_period_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('pengampu_kelas', function (Blueprint $table) {
            $table->dropUnique('pengampu_kelas_period_unique');
            $table->unique(['guru_mapel_id', 'kelas_id'], 'pengampu_kelas_guru_mapel_id_kelas_id_unique');
        });

        Schema::table('pengampu_kelas', function (Blueprint $table) {
            $table->dropIndex('pengampu_kelas_guru_mapel_idx');
            $table->dropIndex('pengampu_kelas_kelas_idx');
            if (Schema::hasColumn('pengampu_kelas', 'semester')) {
                $table->dropColumn('semester');
            }
            if (Schema::hasColumn('pengampu_kelas', 'tahun_ajaran')) {
                $table->dropColumn('tahun_ajaran');
            }
        });
    }
};
