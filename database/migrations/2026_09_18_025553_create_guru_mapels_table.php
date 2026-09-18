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
        Schema::create('guru_mapels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_guru')
                ->constrained('guru');

            $table->foreignId('id_mapel')
                ->constrained('mapels');

            $table->foreignId('id_kelas')
                ->constrained('kelas');

            $table->unique(['id_guru', 'id_mapel', 'id_kelas']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guru_mapels');
    }
};
