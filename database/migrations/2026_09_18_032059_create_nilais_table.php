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
        Schema::create('nilais', function (Blueprint $table) {
            $table->id();
            $table->decimal('nilai', 5, 2);
            $table->enum('status', ['telat', 'selesai']);
            $table->integer('durasi')->nullable();
            $table->string('file_jawaban', 255)->nullable();

            $table->foreignId('id_siswa')
                ->constrained('siswa');

            $table->foreignId('id_tugas')
                ->nullable()
                ->constrained('tugas');

            $table->foreignId('id_quiz')
                ->nullable()
                ->constrained('quizzes');

            $table->foreignId('id_seleksi_ujian')
                ->nullable()
                ->constrained('seleksi_ujians');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilais');
    }
};
