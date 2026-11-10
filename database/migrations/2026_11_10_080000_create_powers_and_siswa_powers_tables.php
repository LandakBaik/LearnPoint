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
        // A. Tabel Baru 1: powers (Master Katalog Power)
        Schema::create('powers', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->unique();
            $table->string('nama', 100);
            $table->string('tagline', 100);
            $table->text('deskripsi');
            $table->text('detail_efek')->nullable();
            $table->string('tipe_efek', 50); // multiplier, second_chance, hint, eliminate_options
            $table->unsignedInteger('stok_awal')->default(2);
            $table->string('icon', 100)->nullable();
            $table->string('tema_warna', 30)->nullable(); // rose, emerald, teal, amber, indigo
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // B. Tabel Baru 2: siswa_powers (Inventori / Tas Power Siswa)
        Schema::create('siswa_powers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')
                ->constrained('siswas')
                ->cascadeOnDelete();
            $table->foreignId('power_id')
                ->constrained('powers')
                ->cascadeOnDelete();
            $table->unsignedInteger('jumlah')->default(0);
            $table->timestamps();

            $table->unique(['siswa_id', 'power_id']);
        });

        // C. Penyesuaian Tabel Existing: menjawabs
        Schema::table('menjawabs', function (Blueprint $table) {
            $table->foreignId('power_id')
                ->nullable()
                ->after('siswa_id')
                ->constrained('powers')
                ->nullOnDelete();

            $table->decimal('poin_diperoleh', 6, 2)
                ->default(0)
                ->after('benar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menjawabs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('power_id');
            $table->dropColumn('poin_diperoleh');
        });

        Schema::dropIfExists('siswa_powers');
        Schema::dropIfExists('powers');
    }
};

