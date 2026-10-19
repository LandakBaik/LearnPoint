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
        Schema::table('quizzes', function (Blueprint $table) {
            $table->text('deskripsi')->nullable()->after('judul');
            $table->string('status', 20)->default('draft')->after('deskripsi'); // 'draft' / 'published'
            $table->json('kesulitan_aktif')->nullable()->after('status'); // e.g. ["Mudah", "Sedang", "Sulit"]
            $table->string('level', 50)->nullable()->change();
            $table->string('kesulitan', 50)->nullable()->change();
        });

        Schema::table('soals', function (Blueprint $table) {
            $table->string('kesulitan', 50)->nullable()->after('quiz_id'); // 'Mudah', 'Sedang', 'Sulit'
            $table->string('level', 50)->nullable()->after('kesulitan'); // 'Level 1' s/d 'Level 5'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('soals', function (Blueprint $table) {
            $table->dropColumn(['kesulitan', 'level']);
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['deskripsi', 'status', 'kesulitan_aktif']);
            $table->string('level', 50)->nullable(false)->change();
            $table->string('kesulitan', 50)->nullable(false)->change();
        });
    }
};
