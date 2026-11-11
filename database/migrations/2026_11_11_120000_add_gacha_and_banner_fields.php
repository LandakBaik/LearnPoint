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
        // 1. Kolom baru di tabel siswas untuk Koin, Pity, dan Banner Dashboard
        Schema::table('siswas', function (Blueprint $table) {
            $table->unsignedInteger('koin')->default(100)->after('status');
            $table->unsignedTinyInteger('pity_gacha')->default(0)->after('koin');
            $table->string('equipped_banner', 50)->default('default')->after('pity_gacha');
            $table->json('unlocked_banners')->nullable()->after('equipped_banner');
        });

        // 2. Kolom baru di tabel menjawabs untuk mencegah farming koin berulang
        Schema::table('menjawabs', function (Blueprint $table) {
            $table->boolean('koin_diklaim')->default(false)->after('poin_diperoleh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menjawabs', function (Blueprint $table) {
            $table->dropColumn('koin_diklaim');
        });

        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn(['koin', 'pity_gacha', 'equipped_banner', 'unlocked_banners']);
        });
    }
};
