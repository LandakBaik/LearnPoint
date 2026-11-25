<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * - `mapels.asset`: Index pada array `planetGroups` di aplikasi mobile (0 = Planet 1 Chess, 1 = Planet 2 Void, 2 = Planet 3 Ribbon, 3 = Planet 4 Sun).
     * - `quizzes.asset`: Index pada array `scenes` (`QuizScenePreset`) di dalam `planetGroups[mapel.asset]` milik planet mata pelajaran tersebut.
     */
    public function up(): void
    {
        Schema::table('mapels', function (Blueprint $table) {
            if (!Schema::hasColumn('mapels', 'asset')) {
                $table->unsignedTinyInteger('asset')->default(0)->after('tipe_mapel');
            }
        });

        Schema::table('quizzes', function (Blueprint $table) {
            if (!Schema::hasColumn('quizzes', 'asset')) {
                $table->unsignedTinyInteger('asset')->default(0)->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            if (Schema::hasColumn('quizzes', 'asset')) {
                $table->dropColumn('asset');
            }
        });

        Schema::table('mapels', function (Blueprint $table) {
            if (Schema::hasColumn('mapels', 'asset')) {
                $table->dropColumn('asset');
            }
        });
    }
};
