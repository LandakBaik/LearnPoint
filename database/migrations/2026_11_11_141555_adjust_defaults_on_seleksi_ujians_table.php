<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('seleksi_ujians', function (Blueprint $table) {
            $table->decimal('nilai_awal', 5, 2)->default(0)->change();
            $table->decimal('nilai_akhir', 5, 2)->default(0)->change();
            $table->integer('jumlah_remidi')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
