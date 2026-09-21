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
        Schema::create('anggota_kelases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')
                ->constrained('kelases')
                ->cascadeOnDelete();

            $table->foreignId('siswa_id')
                ->constrained('siswas')
                ->cascadeOnDelete();

            $table->string('tahun_ajaran', 20)->default('2026/2027');
            $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
            $table->string('jadwal')->nullable(); // Path foto jadwal kelas
            $table->timestamps();

            $table->unique(['kelas_id', 'siswa_id', 'tahun_ajaran', 'semester']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anggota_kelases');
    }
};
