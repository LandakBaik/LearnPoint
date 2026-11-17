<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seleksi_ujians', function (Blueprint $table) {
            if (!Schema::hasColumn('seleksi_ujians', 'waktu_mulai')) {
                $table->dateTime('waktu_mulai')->nullable()->after('siswa_id');
            }

            if (!Schema::hasColumn('seleksi_ujians', 'waktu_selesai')) {
                $table->dateTime('waktu_selesai')->nullable()->after('waktu_mulai');
            }

            if (!Schema::hasColumn('seleksi_ujians', 'status')) {
                $table->string('status', 50)->default('sedang_mengerjakan')->after('waktu_selesai');
            }

            if (!Schema::hasColumn('seleksi_ujians', 'pelanggaran_count')) {
                $table->unsignedSmallInteger('pelanggaran_count')->default(0)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('seleksi_ujians', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('seleksi_ujians', 'waktu_mulai') ? 'waktu_mulai' : null,
                Schema::hasColumn('seleksi_ujians', 'waktu_selesai') ? 'waktu_selesai' : null,
                Schema::hasColumn('seleksi_ujians', 'status') ? 'status' : null,
                Schema::hasColumn('seleksi_ujians', 'pelanggaran_count') ? 'pelanggaran_count' : null,
            ]));
        });
    }
};
