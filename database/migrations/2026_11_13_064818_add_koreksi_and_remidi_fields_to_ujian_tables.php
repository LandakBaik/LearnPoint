<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom skor_essay & catatan_guru pada tabel menjawabs
        // Catatan: skor_essay = NULL berarti BELUM dikoreksi guru (Status Kuning)
        Schema::table('menjawabs', function (Blueprint $table) {
            $table->decimal('skor_essay', 8, 2)->nullable()->after('benar');
            $table->text('catatan_guru')->nullable()->after('skor_essay');
        });

        // 2. Tambah kolom jenis ujian, ujian induk, kebijakan remidi, dan daftar peserta khusus
        Schema::table('ujians', function (Blueprint $table) {
            $table->string('jenis_ujian', 20)->default('reguler')->after('status'); // reguler, remidi, susulan
            $table->unsignedBigInteger('parent_ujian_id')->nullable()->after('jenis_ujian');
            $table->string('kebijakan_remidi', 20)->nullable()->after('parent_ujian_id'); // kkm, terbaik, rata_rata
            $table->json('peserta_khusus')->nullable()->after('kebijakan_remidi'); // array siswa_id khusus remidi/susulan

            $table->foreign('parent_ujian_id')->references('id')->on('ujians')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $table->dropForeign(['parent_ujian_id']);
            $table->dropColumn(['jenis_ujian', 'parent_ujian_id', 'kebijakan_remidi', 'peserta_khusus']);
        });

        Schema::table('menjawabs', function (Blueprint $table) {
            $table->dropColumn(['skor_essay', 'catatan_guru']);
        });
    }
};
