<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_periods', function (Blueprint $table) {
            $table->id();
            $table->string('tahun_ajaran', 20);
            $table->enum('semester', ['ganjil', 'genap']);
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique(['tahun_ajaran', 'semester']);
        });

        // Seed default active academic period so existing flows remain compatible
        DB::table('academic_periods')->insert([
            'tahun_ajaran'    => '2026/2027',
            'semester'        => 'ganjil',
            'tanggal_mulai'   => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_active'       => true,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_periods');
    }
};
