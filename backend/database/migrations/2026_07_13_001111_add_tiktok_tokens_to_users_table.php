<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tiktok_access_token')->nullable()->after('tiktok_username');
            $table->string('tiktok_refresh_token')->nullable()->after('tiktok_access_token');
            $table->timestamp('tiktok_token_expires_at')->nullable()->after('tiktok_refresh_token');
            $table->string('tiktok_open_id')->nullable()->after('tiktok_token_expires_at')->unique();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['tiktok_access_token', 'tiktok_refresh_token', 'tiktok_token_expires_at', 'tiktok_open_id']);
        });
    }
};
