<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->string('video_url')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->bigInteger('views')->default(0);
            $table->bigInteger('likes')->default(0);
            $table->bigInteger('comments')->default(0);
            $table->bigInteger('shares')->default(0);
            $table->string('status', 20)->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
