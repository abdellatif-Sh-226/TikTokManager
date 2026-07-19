<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('tiktok_publish_id')->nullable()->after('status');
            $table->string('tiktok_status', 30)->nullable()->after('tiktok_publish_id');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['tiktok_publish_id', 'tiktok_status']);
        });
    }
};
