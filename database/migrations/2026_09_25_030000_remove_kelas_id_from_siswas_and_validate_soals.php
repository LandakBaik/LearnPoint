<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('siswas', 'kelas_id')) {
            Schema::table('siswas', function (Blueprint $table) {
                $table->dropForeign(['kelas_id']);
                $table->dropColumn('kelas_id');
            });
        }

        $checkExpression = '(CASE WHEN quiz_id IS NOT NULL THEN 1 ELSE 0 END) + (CASE WHEN tugas_id IS NOT NULL THEN 1 ELSE 0 END) + (CASE WHEN ujian_id IS NOT NULL THEN 1 ELSE 0 END) = 1';

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE soals ADD CONSTRAINT soals_exactly_one_activity_fk CHECK ($checkExpression)");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE soals DROP CONSTRAINT soals_exactly_one_activity_fk');
        }

        Schema::table('siswas', function (Blueprint $table) {
            $table->foreignId('kelas_id')
                ->nullable()
                ->constrained('kelases')
                ->nullOnDelete();
        });
    }
};
