<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materis', function (Blueprint $table) {
            $table->text('deskripsi')->nullable()->after('judul');
            $table->string('url_youtube', 2048)->nullable()->after('file_materi');
        });
    }

    public function down(): void
    {
        Schema::table('materis', function (Blueprint $table) {
            $table->dropColumn(['deskripsi', 'url_youtube']);
        });
    }
};
