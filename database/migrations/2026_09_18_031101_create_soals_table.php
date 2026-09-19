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

            $table->foreignId('quiz_id')
                ->nullable()
                ->constrained('quizzes')
                ->cascadeOnDelete();

            $table->foreignId('tugas_id')
                ->nullable()
                ->constrained('tugases')
                ->cascadeOnDelete();

            $table->foreignId('ujian_id')
                ->nullable()
                ->constrained('ujians')
                ->cascadeOnDelete();
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
