
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $table->string('id_grup_ujian', 36)
                ->nullable()
                ->after('id')
                ->index();
        });

        // Ujian lama mendapatkan grup masing-masing.
        // Jadi data lama tidak otomatis tergabung.
        DB::table('ujians')
            ->whereNull('id_grup_ujian')
            ->orderBy('id')
            ->each(function ($ujian) {
                DB::table('ujians')
                    ->where('id', $ujian->id)
                    ->update([
                        'id_grup_ujian' => (string) Str::uuid(),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $table->dropIndex(['id_grup_ujian']);
            $table->dropColumn('id_grup_ujian');
        });
    }
};
