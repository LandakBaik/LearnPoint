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
        Schema::create('pengampu_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_mapel_id')
                ->constrained('guru_mapels')
                ->cascadeOnDelete();

            $table->foreignId('kelas_id')
                ->constrained('kelases')
                ->cascadeOnDelete();

            $table->unique(['guru_mapel_id', 'kelas_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengampu_kelas');
    }
};
