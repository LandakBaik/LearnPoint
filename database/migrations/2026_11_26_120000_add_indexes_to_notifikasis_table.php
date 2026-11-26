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
        Schema::table('notifikasis', function (Blueprint $table) {
            $table->index(['user_id', 'is_read', 'created_at'], 'idx_notif_user_read_created');
            $table->index(['user_id', 'tipe'], 'idx_notif_user_tipe');
            $table->index(['tipe', 'id_referensi'], 'idx_notif_tipe_referensi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifikasis', function (Blueprint $table) {
            $table->dropIndex('idx_notif_user_read_created');
            $table->dropIndex('idx_notif_user_tipe');
            $table->dropIndex('idx_notif_tipe_referensi');
        });
    }
};
