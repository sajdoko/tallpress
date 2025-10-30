<?php

namespace Sajdoko\TallPress\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Sajdoko\TallPress\Models\Comment;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'post_id' => null, // Should be set when creating
            'author_name' => fake()->name(),
            'author_email' => fake()->safeEmail(),
            'body' => $this->generateRealisticComment(),
            'approved' => fake()->boolean(80),
        ];
    }

    /**
     * Generate a realistic blog comment
     */
    protected function generateRealisticComment(): string
    {
        $commentTemplates = [
            'Great article! {extra}',
            'Thanks for sharing this! {extra}',
            'This is exactly what I needed. {extra}',
            'Very helpful guide. {extra}',
            'Excellent post! {extra}',
            'I learned so much from this. {extra}',
            'This is really well explained. {extra}',
            'Interesting perspective! {extra}',
            'Appreciate the detailed breakdown. {extra}',
            'Bookmarking this for later! {extra}',
        ];

        $extras = [
            'Looking forward to more content like this.',
            'I\'ll definitely try this approach.',
            'Do you have any recommendations for beginners?',
            'This answered so many of my questions.',
            'Keep up the great work!',
            'Would love to see a follow-up post on this topic.',
            'The examples were really helpful.',
            'This made a complex topic easy to understand.',
            '',
        ];

        $template = fake()->randomElement($commentTemplates);
        $extra = fake()->randomElement($extras);

        return str_replace('{extra}', $extra, $template);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved' => true,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved' => false,
        ]);
    }
}
