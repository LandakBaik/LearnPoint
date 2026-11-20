<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Pastikan semua pasangan (tahun_ajaran, semester) yang sudah ada tercatat di academic_periods
        $existingPairs = collect();

        if (Schema::hasTable('anggota_kelases') && Schema::hasColumn('anggota_kelases', 'tahun_ajaran')) {
            $existingPairs = $existingPairs->merge(
                DB::table('anggota_kelases')->select('tahun_ajaran', 'semester')->distinct()->get()
            );
        }

        if (Schema::hasTable('pengampu_kelas') && Schema::hasColumn('pengampu_kelas', 'tahun_ajaran')) {
            $existingPairs = $existingPairs->merge(
                DB::table('pengampu_kelas')->select('tahun_ajaran', 'semester')->distinct()->get()
            );
        }

        foreach ($existingPairs as $pair) {
            if (empty($pair->tahun_ajaran) || empty($pair->semester)) {
                continue;
            }
            $sem = strtolower((string) $pair->semester);
            if (!in_array($sem, ['ganjil', 'genap'], true)) {
                $sem = 'ganjil';
            }

            $exists = DB::table('academic_periods')
                ->where('tahun_ajaran', $pair->tahun_ajaran)
                ->where('semester', $sem)
                ->exists();

            if (!$exists) {
                DB::table('academic_periods')->insert([
                    'tahun_ajaran' => $pair->tahun_ajaran,
                    'semester'     => $sem,
                    'is_active'    => false,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }

        $defaultPeriodId = DB::table('academic_periods')->where('is_active', true)->value('id')
            ?? DB::table('academic_periods')->orderBy('id')->value('id');

        // 2. Normalisasi anggota_kelases -> academic_period_id
        if (Schema::hasTable('anggota_kelases') && !Schema::hasColumn('anggota_kelases', 'academic_period_id')) {
            Schema::table('anggota_kelases', function (Blueprint $table) {
                $table->foreignId('academic_period_id')
                    ->nullable()
                    ->after('siswa_id')
                    ->constrained('academic_periods')
                    ->restrictOnDelete();
            });

            $rows = DB::table('anggota_kelases')->get(['id', 'tahun_ajaran', 'semester']);
            foreach ($rows as $row) {
                $periodId = DB::table('academic_periods')
                    ->where('tahun_ajaran', $row->tahun_ajaran)
                    ->where('semester', strtolower((string) $row->semester))
                    ->value('id') ?? $defaultPeriodId;

                DB::table('anggota_kelases')->where('id', $row->id)->update(['academic_period_id' => $periodId]);
            }

            // Pastikan index FK tersedia sebelum drop unique lama
            Schema::table('anggota_kelases', function (Blueprint $table) {
                $table->index('kelas_id', 'anggota_kelases_kelas_idx');
                $table->index('siswa_id', 'anggota_kelases_siswa_idx');
            });

            Schema::table('anggota_kelases', function (Blueprint $table) {
                $table->dropUnique('anggota_kelases_kelas_id_siswa_id_tahun_ajaran_semester_unique');
            });

            Schema::table('anggota_kelases', function (Blueprint $table) {
                $table->dropColumn(['tahun_ajaran', 'semester']);
            });

            Schema::table('anggota_kelases', function (Blueprint $table) {
                $table->unsignedBigInteger('academic_period_id')->nullable(false)->change();
                $table->unique(['siswa_id', 'academic_period_id'], 'anggota_kelases_siswa_period_unique');
            });
        }

        // 3. Normalisasi pengampu_kelas -> academic_period_id
        if (Schema::hasTable('pengampu_kelas') && !Schema::hasColumn('pengampu_kelas', 'academic_period_id')) {
            Schema::table('pengampu_kelas', function (Blueprint $table) {
                $table->foreignId('academic_period_id')
                    ->nullable()
                    ->after('kelas_id')
                    ->constrained('academic_periods')
                    ->restrictOnDelete();
            });

            $rows = DB::table('pengampu_kelas')->get(['id', 'tahun_ajaran', 'semester']);
            foreach ($rows as $row) {
                $periodId = DB::table('academic_periods')
                    ->where('tahun_ajaran', $row->tahun_ajaran)
                    ->where('semester', strtolower((string) $row->semester))
                    ->value('id') ?? $defaultPeriodId;

                DB::table('pengampu_kelas')->where('id', $row->id)->update(['academic_period_id' => $periodId]);
            }

            Schema::table('pengampu_kelas', function (Blueprint $table) {
                $table->dropUnique('pengampu_kelas_period_unique');
            });

            Schema::table('pengampu_kelas', function (Blueprint $table) {
                $table->dropColumn(['tahun_ajaran', 'semester']);
            });

            Schema::table('pengampu_kelas', function (Blueprint $table) {
                $table->unsignedBigInteger('academic_period_id')->nullable(false)->change();
                $table->unique(
                    ['guru_mapel_id', 'kelas_id', 'academic_period_id'],
                    'pengampu_kelas_period_unique'
                );
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pengampu_kelas') && Schema::hasColumn('pengampu_kelas', 'academic_period_id')) {
            Schema::table('pengampu_kelas', function (Blueprint $table) {
                $table->dropUnique('pengampu_kelas_period_unique');
                $table->string('tahun_ajaran', 20)->default('2026/2027')->after('kelas_id');
                $table->string('semester', 10)->default('ganjil')->after('tahun_ajaran');
            });

            $rows = DB::table('pengampu_kelas')->get(['id', 'academic_period_id']);
            foreach ($rows as $row) {
                $p = DB::table('academic_periods')->find($row->academic_period_id);
                if ($p) {
                    DB::table('pengampu_kelas')->where('id', $row->id)->update([
                        'tahun_ajaran' => $p->tahun_ajaran,
                        'semester'     => $p->semester,
                    ]);
                }
            }

            Schema::table('pengampu_kelas', function (Blueprint $table) {
                $table->dropForeign(['academic_period_id']);
                $table->dropColumn('academic_period_id');
                $table->unique(['guru_mapel_id', 'kelas_id', 'tahun_ajaran', 'semester'], 'pengampu_kelas_period_unique');
            });
        }

        if (Schema::hasTable('anggota_kelases') && Schema::hasColumn('anggota_kelases', 'academic_period_id')) {
            Schema::table('anggota_kelases', function (Blueprint $table) {
                $table->dropUnique('anggota_kelases_siswa_period_unique');
                $table->string('tahun_ajaran', 20)->default('2026/2027')->after('siswa_id');
                $table->enum('semester', ['ganjil', 'genap'])->default('ganjil')->after('tahun_ajaran');
            });

            $rows = DB::table('anggota_kelases')->get(['id', 'academic_period_id']);
            foreach ($rows as $row) {
                $p = DB::table('academic_periods')->find($row->academic_period_id);
                if ($p) {
                    DB::table('anggota_kelases')->where('id', $row->id)->update([
                        'tahun_ajaran' => $p->tahun_ajaran,
                        'semester'     => $p->semester,
                    ]);
                }
            }

            Schema::table('anggota_kelases', function (Blueprint $table) {
                $table->dropForeign(['academic_period_id']);
                $table->dropColumn('academic_period_id');
                $table->unique(['kelas_id', 'siswa_id', 'tahun_ajaran', 'semester'], 'anggota_kelases_kelas_id_siswa_id_tahun_ajaran_semester_unique');
            });
        }
    }
};
