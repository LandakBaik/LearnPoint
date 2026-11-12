<?php

use App\Models\Item;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel Master Item Game (Pure Unified Item Architecture)
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->unique();
            $table->string('nama', 100);
            $table->string('kategori', 30)->default('item'); // 'power', 'banner', 'skin', dll
            $table->text('deskripsi')->nullable();
            $table->string('rarity', 20)->default('common'); // common, rare, epic, legendary
            $table->unsignedInteger('bobot_gacha')->default(20);
            $table->boolean('is_stackable')->default(true);
            $table->string('asset_url', 255)->nullable();
            $table->json('atribut')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['kategori', 'is_active']);
        });

        // 2. Isi Katalog Item Default (5 Power + 7 Banner GIF)
        foreach (Item::DEFAULT_ITEMS as $itemData) {
            $attributes = is_array($itemData['atribut']) ? json_encode($itemData['atribut']) : $itemData['atribut'];
            DB::table('items')->insert([
                'id'           => $itemData['id'] ?? null,
                'kode'         => $itemData['kode'],
                'nama'         => $itemData['nama'],
                'kategori'     => $itemData['kategori'],
                'deskripsi'    => $itemData['deskripsi'],
                'rarity'       => $itemData['rarity'] ?? 'common',
                'bobot_gacha'  => $itemData['bobot_gacha'] ?? 20,
                'is_stackable' => $itemData['is_stackable'] ?? true,
                'asset_url'    => $itemData['asset_url'] ?? null,
                'atribut'      => $attributes,
                'is_active'    => $itemData['is_active'] ?? true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // 3. Tabel Inventori Siswa (Tas Kepemilikan Power & Banner)
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')
                ->constrained('siswas')
                ->cascadeOnDelete();
            $table->foreignId('item_id')
                ->constrained('items')
                ->cascadeOnDelete();
            
            // Kuantitas item (untuk power = stok, untuk banner/kosmetik = 1)
            $table->unsignedInteger('jumlah')->default(1);
            
            // Status aktif terpasang (khusus kosmetik/banner dashboard)
            $table->boolean('is_equipped')->default(false);
            
            // Metadata opsional (konfigurasi kustom / durasi / dll)
            $table->json('metadata')->nullable();
            
            $table->timestamps();

            $table->unique(['siswa_id', 'item_id']);
            $table->index(['siswa_id', 'is_equipped']);
        });

        // 4. Penyesuaian Tabel Existing: menjawabs
        Schema::table('menjawabs', function (Blueprint $table) {
            $table->foreignId('power_id')
                ->nullable()
                ->after('siswa_id')
                ->constrained('items')
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
            $table->dropForeign(['power_id']);
            $table->dropColumn(['power_id', 'poin_diperoleh']);
        });

        Schema::dropIfExists('inventories');
        Schema::dropIfExists('items');
    }
};
