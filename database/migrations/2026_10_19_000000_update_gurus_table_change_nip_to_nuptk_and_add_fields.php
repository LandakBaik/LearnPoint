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
            $table->renameColumn('nip', 'nuptk');
        });

        Schema::table('gurus', function (Blueprint $table) {
            $table->string('nuptk', 20)->change();
            $table->string('nomor_telepon', 20)->nullable()->after('nuptk');
            $table->string('jenis_kelamin', 20)->nullable()->after('nomor_telepon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gurus', function (Blueprint $table) {
            $table->dropColumn(['nomor_telepon', 'jenis_kelamin']);
            $table->renameColumn('nuptk', 'nip');
        });

        Schema::table('gurus', function (Blueprint $table) {
            $table->string('nip', 18)->change();
        });
    }
};
