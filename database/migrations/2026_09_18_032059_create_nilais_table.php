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

            $table->foreignId('siswa_id')
                ->constrained('siswas')
                ->cascadeOnDelete();

            $table->foreignId('tugas_id')
                ->nullable()
                ->constrained('tugases')
                ->cascadeOnDelete();

            $table->foreignId('quiz_id')
                ->nullable()
                ->constrained('quizzes')
                ->cascadeOnDelete();

            $table->foreignId('seleksi_ujian_id')
                ->nullable()
                ->constrained('seleksi_ujians')
                ->cascadeOnDelete();
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
