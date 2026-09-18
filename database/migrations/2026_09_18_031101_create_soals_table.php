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
        Schema::create('soals', function (Blueprint $table) {
            $table->id();
            $table->text('pertanyaan');
            $table->text('pilihan');
            $table->string('kunci_jawaban', 10);

            $table->foreignId('id_quiz')
                ->nullable()
                ->constrained('quizzes');

            $table->foreignId('id_tugas')
                ->nullable()
                ->constrained('tugas');

            $table->foreignId('id_ujian')
                ->nullable()
                ->constrained('ujians');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soals');
    }
};
