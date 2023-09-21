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
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->integer('story_id')->unique();
            $table->foreignId('author_id')->constrained('authors')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('descendants');
            $table->integer('score');
            $table->dateTime('time', $precision = 0);
            $table->string('title');
            $table->string('type');
            $table->string('url');
            $table->json('kids')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
