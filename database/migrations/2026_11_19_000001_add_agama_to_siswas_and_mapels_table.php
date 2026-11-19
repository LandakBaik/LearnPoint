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
        Schema::table('siswas', function (Blueprint $table) {
            if (!Schema::hasColumn('siswas', 'agama')) {
                $table->string('agama', 30)->nullable()->after('jenis_kelamin');
            }
        });

        Schema::table('mapels', function (Blueprint $table) {
            if (!Schema::hasColumn('mapels', 'agama')) {
                $table->string('agama', 30)->nullable()->after('kkm');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            if (Schema::hasColumn('siswas', 'agama')) {
                $table->dropColumn('agama');
            }
        });

        Schema::table('mapels', function (Blueprint $table) {
            if (Schema::hasColumn('mapels', 'agama')) {
                $table->dropColumn('agama');
            }
        });
    }
};
