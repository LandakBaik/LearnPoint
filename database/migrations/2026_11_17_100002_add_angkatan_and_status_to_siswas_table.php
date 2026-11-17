<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            if (!Schema::hasColumn('siswas', 'angkatan')) {
                $table->unsignedSmallInteger('angkatan')->nullable()->after('nis');
            }
        });

        // Ubah kolom status menjadi string agar mendukung 'aktif', 'nonaktif', 'lulus', 'pindah'
        // secara kompatibel di MySQL maupun SQLite.
        if (Schema::hasColumn('siswas', 'status')) {
            Schema::table('siswas', function (Blueprint $table) {
                $table->string('status', 20)->default('aktif')->change();
            });
        }

        // Backfill angkatan dari riwayat anggota_kelases paling awal jika tersedia
        $siswas = DB::table('siswas')->select('id', 'angkatan')->get();
        foreach ($siswas as $s) {
            if ($s->angkatan !== null) {
                continue;
            }
            $earliestTa = DB::table('anggota_kelases')
                ->where('siswa_id', $s->id)
                ->orderBy('tahun_ajaran')
                ->value('tahun_ajaran');

            $year = 2026;
            if ($earliestTa && preg_match('/^(\d{4})/', $earliestTa, $m)) {
                $year = (int) $m[1];
            }
            DB::table('siswas')->where('id', $s->id)->update(['angkatan' => $year]);
        }
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            if (Schema::hasColumn('siswas', 'angkatan')) {
                $table->dropColumn('angkatan');
            }
        });
    }
};
