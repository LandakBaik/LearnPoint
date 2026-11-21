<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $table->unsignedTinyInteger('sesi')->default(1)->after('pengampu_kelas_id');
            $table->string('token', 10)->nullable()->after('status');
            $table->timestamp('waktu_mulai_sesi')->nullable()->after('waktu_mulai');
        });
    }

    public function down(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $table->dropColumn(['sesi', 'token', 'waktu_mulai_sesi']);
        });
    }
};
