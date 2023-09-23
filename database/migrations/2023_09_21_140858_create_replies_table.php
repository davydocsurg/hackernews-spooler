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
        Schema::create('replies', function (Blueprint $table) {
            $table->id();
            $table->integer('reply_id')->unique();
            $table->text('text');
            $table->dateTime('time', $precision = 0);
            $table->string('type');
            $table->foreignId('story_id')->constrained('stories')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('author_id')->constrained('authors')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('parent_comment_id')->constrained('comments')->onUpdate('cascade')->onDelete('cascade')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('replies');
    }
};
