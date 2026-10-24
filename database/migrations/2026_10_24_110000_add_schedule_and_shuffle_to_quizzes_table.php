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
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dateTime('waktu_mulai')->nullable()->after('kesulitan_aktif');
            $table->dateTime('waktu_selesai')->nullable()->after('waktu_mulai');
            $table->boolean('acak_jawaban')->default(false)->after('waktu_selesai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['waktu_mulai', 'waktu_selesai', 'acak_jawaban']);
        });
    }
};
