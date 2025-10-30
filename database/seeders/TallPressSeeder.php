<?php

namespace Sajdoko\TallPress\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Sajdoko\TallPress\Models\Category;
use Sajdoko\TallPress\Models\Comment;
use Sajdoko\TallPress\Models\Post;
use Sajdoko\TallPress\Models\Tag;

class TallPressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if ($this->command) {
            $this->command->info('Seeding blog data...');
        }

        // Get or create users for authors
        $authorModel = config('tallpress.author_model', 'App\\Models\\User');

        // Check if any users exist
        if ($authorModel::count() === 0) {
            if ($this->command) {
                $this->command->info('No users found. Creating sample users...');
            }
            $this->call(TallPressAdminSeeder::class);
        }

        $author = $authorModel::first();

        if (! $author) {
            if ($this->command) {
                $this->command->error('Failed to create or find users. Please check your User model configuration.');
            }

            return;
        }

        DB::transaction(function () use ($author) {
            // Create categories
            $categories = Category::factory(5)->create();
            if ($this->command) {
                $this->command->info('Created 5 categories');
            }

            // Create tags
            $tags = Tag::factory(10)->create();
            if ($this->command) {
                $this->command->info('Created 10 tags');
            }

            // Create published posts
            Post::factory(15)
                ->published()
                ->create(['author_id' => $author->id])
                ->each(function ($post) use ($categories, $tags) {
                    // Attach random categories
                    $post->categories()->attach(
                        $categories->random(rand(1, 3))->pluck('id')
                    );

                    // Attach random tags
                    $post->tags()->attach(
                        $tags->random(rand(2, 5))->pluck('id')
                    );

                    // Create comments for some posts
                    if (rand(0, 100) > 30) {
                        Comment::factory(rand(1, 8))
                            ->create(['post_id' => $post->id]);
                    }
                });
            if ($this->command) {
                $this->command->info('Created 15 published posts');
            }

            // Create draft posts
            Post::factory(5)
                ->draft()
                ->create(['author_id' => $author->id])
                ->each(function ($post) use ($categories, $tags) {
                    $post->categories()->attach(
                        $categories->random(rand(1, 2))->pluck('id')
                    );

                    $post->tags()->attach(
                        $tags->random(rand(1, 3))->pluck('id')
                    );
                });
            if ($this->command) {
                $this->command->info('Created 5 draft posts');
            }
        });

        if ($this->command) {
            $this->command->info('Blog seeding completed successfully!');
        }
    }
}
