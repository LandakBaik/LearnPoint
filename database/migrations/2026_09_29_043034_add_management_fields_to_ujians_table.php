<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            if (!Schema::hasColumn('ujians', 'kisi_kisi')) {
                $table->text('kisi_kisi')->nullable();
            }

            if (!Schema::hasColumn('ujians', 'kkm')) {
                $table->decimal('kkm', 5, 2)->default(75);
            }

            if (!Schema::hasColumn('ujians', 'status')) {
                $table->enum('status', [
                    'draft',
                    'scheduled',
                    'published',
                    'completed',
                ])->default('draft');
            }

            if (!Schema::hasColumn('ujians', 'dipublikasi_at')) {
                $table->dateTime('dipublikasi_at')->nullable();
            }

            // Draft boleh belum memiliki tanggal ujian.
            if (Schema::hasColumn('ujians', 'deadline')) {
                $table->dateTime('deadline')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $columns = ['kisi_kisi', 'kkm', 'status', 'dipublikasi_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('ujians', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('ujians', 'deadline')) {
                $table->dateTime('deadline')->nullable(false)->change();
            }
        });
    }
};
