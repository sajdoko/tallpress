<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tallpress_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('tallpress_posts')->cascadeOnDelete();
            $table->string('author_name');
            $table->string('author_email');
            $table->text('body');
            $table->boolean('approved')->default(false);
            $table->timestamps();

            $table->index('post_id');
            $table->index('approved');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tallpress_comments');
    }
};
