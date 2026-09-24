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
        Schema::table('gurus', function (Blueprint $table) {
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->after('nip');
        });

        Schema::table('siswas', function (Blueprint $table) {
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->after('nohp_wali');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gurus', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
