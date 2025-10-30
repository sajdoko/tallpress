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
        Schema::create('tallpress_post_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('tallpress_posts')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->longText('body');
            $table->json('meta')->nullable();
            $table->timestamp('created_at');

            $table->index(['post_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tallpress_post_revisions');
    }
};
