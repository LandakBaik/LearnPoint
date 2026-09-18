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
        Schema::create('seleksi_ujians', function (Blueprint $table) {
            $table->id();
            $table->decimal('nilai_awal', 5, 2);
            $table->integer('jumlah_remidi')->default(0);
            $table->decimal('nilai_akhir', 5, 2);

            $table->foreignId('id_siswa')
                ->constrained('siswa');

            $table->foreignId('id_ujian')
                ->constrained('ujians');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seleksi_ujians');
    }
};
