<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $table->text('kisi_kisi')->nullable();

            $table->decimal('kkm', 5, 2)->default(75);

            $table->enum('status', [
                'draft',
                'scheduled',
                'published',
                'finished',
            ])->default('draft');

            $table->dateTime('dipublikasi_at')->nullable();

            // Draft boleh belum memiliki tanggal ujian.
            $table->dateTime('deadline')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $table->dropColumn([
                'kisi_kisi',
                'kkm',
                'status',
                'dipublikasi_at',
            ]);

            $table->dateTime('deadline')->nullable(false)->change();
        });
    }
};
