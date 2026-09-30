<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('soals', function (Blueprint $table) {
            $table->enum('tipe_soal', [
                'single_choice',
                'multiple_choice',
                'essay',
                'matching',
            ])->default('single_choice');

            $table->string('gambar')->nullable();

            $table->decimal('bobot', 6, 2)->default(1);

            $table->unsignedInteger('urutan')->default(1);

            // Mendukung kunci jawaban yang lebih panjang.
            $table->text('kunci_jawaban')->change();
        });
    }

    public function down(): void
    {
        Schema::table('soals', function (Blueprint $table) {
            $table->dropColumn([
                'tipe_soal',
                'gambar',
                'bobot',
                'urutan',
            ]);

            $table->string('kunci_jawaban', 10)->change();
        });
    }
};
