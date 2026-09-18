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
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');

            $table->enum('keterangan', [
                'hadir',
                'izin',
                'sakit',
                'alpa'
            ]);

            $table->foreignId('id_siswa')
                ->constrained('siswa');

            $table->foreignId('id_akun')
                ->constrained('akun');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
