<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TIPE_VALUES = [
        'teori',
        'praktik',
        'teori_dan_praktik',
        'agama_islam',
        'agama_kristen',
        'agama_katolik',
        'agama_hindu',
        'agama_buddha',
        'agama_konghucu',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mapels', function (Blueprint $table) {
            $table->enum('tipe_mapel', self::TIPE_VALUES)
                ->default('teori')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mapels', function (Blueprint $table) {
            $table->string('tipe_mapel', 40)
                ->default('teori')
                ->change();
        });
    }
};
