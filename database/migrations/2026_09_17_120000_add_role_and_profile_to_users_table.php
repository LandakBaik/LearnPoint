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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->enum('role', ['siswa', 'guru', 'operator', 'kepala_sekolah'])->default('siswa')->after('password');

            $table->foreignId('guru_id')
                ->nullable()
                ->after('role')
                ->constrained('gurus')
                ->nullOnDelete();

            $table->foreignId('siswa_id')
                ->nullable()
                ->after('guru_id')
                ->constrained('siswas')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['guru_id']);
            $table->dropForeign(['siswa_id']);
            $table->dropColumn(['username', 'role', 'guru_id', 'siswa_id']);
        });
    }
};
