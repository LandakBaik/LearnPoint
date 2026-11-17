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
        Schema::create('ujians', function (Blueprint $table) {
            $table->id();
            $table->uuid('id_grup_ujian')->nullable()->index(); // Untuk mengelompokkan ujian yang dibagikan ke banyak kelas
            $table->string('judul', 200);
            $table->text('kisi_kisi')->nullable();

            $table->dateTime('waktu_mulai')->nullable();
            $table->dateTime('deadline')->nullable(); // Dibuat nullable untuk fleksibilitas draft
            $table->unsignedSmallInteger('durasi_menit')->default(60);
            $table->decimal('kkm', 5, 2)->default(75.00);

            // Enum status lengkap sesuai kebutuhan fitur guru & siswa
            $table->enum('status', [
                'draft',
                'scheduled',
                'published',
                'completed',
                'unpublished'
            ])->default('draft');

            $table->dateTime('dipublikasi_at')->nullable();

            $table->foreignId('pengampu_kelas_id')
                ->constrained('pengampu_kelas')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ujians');
    }
};
