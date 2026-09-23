<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambahkan kolom jadwal pada tabel kelases
        Schema::table('kelases', function (Blueprint $table) {
            $table->string('jadwal')->nullable()->after('tingkatan');
        });

        // 2. Salin data jadwal yang sudah ada di anggota_kelases ke tabel kelases
        if (Schema::hasTable('anggota_kelases') && Schema::hasColumn('anggota_kelases', 'jadwal')) {
            $existingJadwals = DB::table('anggota_kelases')->whereNotNull('jadwal')->get();
            foreach ($existingJadwals as $item) {
                DB::table('kelases')
                    ->where('id', $item->kelas_id)
                    ->whereNull('jadwal')
                    ->update(['jadwal' => $item->jadwal]);
            }

            // 3. Hapus kolom jadwal dari tabel anggota_kelases
            Schema::table('anggota_kelases', function (Blueprint $table) {
                $table->dropColumn('jadwal');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Kembalikan kolom jadwal ke tabel anggota_kelases
        if (Schema::hasTable('anggota_kelases')) {
            Schema::table('anggota_kelases', function (Blueprint $table) {
                $table->string('jadwal')->nullable();
            });
        }

        // 2. Hapus kolom jadwal dari tabel kelases
        Schema::table('kelases', function (Blueprint $table) {
            if (Schema::hasColumn('kelases', 'jadwal')) {
                $table->dropColumn('jadwal');
            }
        });
    }
};
