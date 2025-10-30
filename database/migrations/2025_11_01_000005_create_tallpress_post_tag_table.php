<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tallpress_post_tag', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained('tallpress_posts')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tallpress_tags')->cascadeOnDelete();

            $table->primary(['post_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tallpress_post_tag');
    }
};
