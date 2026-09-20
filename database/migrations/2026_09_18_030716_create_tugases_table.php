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
        Schema::create('tugases', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 200);
            $table->dateTime('deadline');
            $table->enum('tipe', ['upload', 'pilihan_ganda']);

            $table->foreignId('guru_mapel_id')
                ->constrained('guru_mapels')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tugases');
    }
};
