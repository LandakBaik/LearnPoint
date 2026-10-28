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
            $table->string('tipe', 50)->default('info')->after('pesan');
            $table->string('url', 255)->nullable()->after('tipe');
            $table->boolean('is_read')->default(false)->after('url');
            $table->timestamp('read_at')->nullable()->after('is_read');
            $table->json('data')->nullable()->after('read_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifikasis', function (Blueprint $table) {
            $table->dropColumn(['tipe', 'url', 'is_read', 'read_at', 'data']);
        });
    }
};
