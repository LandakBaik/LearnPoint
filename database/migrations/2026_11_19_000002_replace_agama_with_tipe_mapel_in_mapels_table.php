<?php

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
        Schema::table('mapels', function (Blueprint $table) {
            if (!Schema::hasColumn('mapels', 'tipe_mapel')) {
                $table->string('tipe_mapel', 40)->default('teori')->after('kkm');
            }
        });

        if (Schema::hasColumn('mapels', 'agama')) {
            $mapping = [
                'Islam'    => 'agama_islam',
                'Kristen'  => 'agama_kristen',
                'Katolik'  => 'agama_katolik',
                'Hindu'    => 'agama_hindu',
                'Buddha'   => 'agama_buddha',
                'Konghucu' => 'agama_konghucu',
            ];

            foreach ($mapping as $agama => $tipe) {
                DB::table('mapels')
                    ->where('agama', $agama)
                    ->update(['tipe_mapel' => $tipe]);
            }

            Schema::table('mapels', function (Blueprint $table) {
                $table->dropColumn('agama');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mapels', function (Blueprint $table) {
            if (!Schema::hasColumn('mapels', 'agama')) {
                $table->string('agama', 30)->nullable()->after('kkm');
            }
        });

        if (Schema::hasColumn('mapels', 'tipe_mapel')) {
            $reverseMapping = [
                'agama_islam'    => 'Islam',
                'agama_kristen'  => 'Kristen',
                'agama_katolik'  => 'Katolik',
                'agama_hindu'    => 'Hindu',
                'agama_buddha'   => 'Buddha',
                'agama_konghucu' => 'Konghucu',
            ];

            foreach ($reverseMapping as $tipe => $agama) {
                DB::table('mapels')
                    ->where('tipe_mapel', $tipe)
                    ->update(['agama' => $agama]);
            }

            Schema::table('mapels', function (Blueprint $table) {
                $table->dropColumn('tipe_mapel');
            });
        }
    }
};
