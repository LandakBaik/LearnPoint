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
        Schema::create('akun', function (Blueprint $table) {
            $table->id();
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->enum('role', ['siswa', 'guru', 'operator', 'kepala_sekolah']);
            $table->string('nama', 100)->nullable();

            $table->unsignedInteger('guru_id')->nullable();
            $table->foreign('guru_id')->references('id')->on('guru')->nullOnDelete();

            $table->unsignedBigInteger('siswa_id')->nullable();
            $table->foreign('siswa_id')->references('id')->on('siswa')->nullOnDelete();

            $table->timestamps();

            // $table->check("
            //     (role = 'operator'
            //         AND nama IS NOT NULL
            //         AND guru_id IS NULL
            //         AND siswa_id IS NULL)
            //
            //     OR
            //
            //     (role = 'guru'
            //         AND nama IS NOT NULL
            //         AND guru_id IS NOT NULL
            //         AND siswa_id IS NULL)
            //
            //     OR
            //
            //     (role = 'siswa'
            //         AND nama IS NOT NULL
            //         AND guru_id IS NULL
            //         AND siswa_id IS NOT NULL)
            // ");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akun');
    }
};
