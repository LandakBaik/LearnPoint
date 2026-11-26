<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('siswas', 'angkatan')) {
            Schema::table('siswas', function (Blueprint $table) {
                $table->year('angkatan')->nullable()->after('nis');
            });
            return;
        }

        // Clamp invalid out-of-range values before converting to MySQL YEAR (1901-2155)
        DB::table('siswas')
            ->whereNotNull('angkatan')
            ->where(function ($q) {
                $q->where('angkatan', '<', 1901)->orWhere('angkatan', '>', 2155);
            })
            ->update(['angkatan' => null]);

        if (DB::getDriverName() === 'mysql' || DB::getDriverName() === 'mariadb') {
            DB::statement('ALTER TABLE `siswas` MODIFY `angkatan` YEAR NULL');
        } else {
            Schema::table('siswas', function (Blueprint $table) {
                $table->year('angkatan')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('siswas', 'angkatan')) {
            if (DB::getDriverName() === 'mysql' || DB::getDriverName() === 'mariadb') {
                DB::statement('ALTER TABLE `siswas` MODIFY `angkatan` SMALLINT UNSIGNED NULL');
            } else {
                Schema::table('siswas', function (Blueprint $table) {
                    $table->unsignedSmallInteger('angkatan')->nullable()->change();
                });
            }
        }
    }
};
