<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materis', function (Blueprint $table) {
            $table->uuid('id_grub_materi')->index()->after('pengampu_kelas_id');
        });
    }

    public function down(): void
    {
        Schema::table('materis', function (Blueprint $table) {
            $table->dropIndex(['id_grub_materi']);
            $table->dropColumn('id_grub_materi');
        });
    }
};
