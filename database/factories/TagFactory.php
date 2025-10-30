<?php

namespace Sajdoko\TallPress\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Sajdoko\TallPress\Models\Tag;

class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        // Realistic blog tags
        $tags = [
            'Tutorial', 'Guide', 'Tips', 'How-To', 'Best Practices',
            'Review', 'Comparison', 'Beginner', 'Advanced', 'Expert',
            'DIY', 'Quick Tips', 'Step by Step', 'Case Study', 'Interview',
            'News', 'Opinion', 'Analysis', 'Trends', 'Future',
            'Tools', 'Resources', 'Free', 'Premium', 'Open Source',
            'JavaScript', 'Python', 'PHP', 'React', 'Laravel',
            'WordPress', 'SEO', 'Marketing', 'Social Media', 'Content',
            'Design', 'UX', 'UI', 'Mobile', 'Responsive',
            'Security', 'Privacy', 'Performance', 'Optimization', 'Testing',
            'Docker', 'API', 'Database', 'Backend', 'Frontend',
            'iOS', 'Android', 'Flutter', 'Swift', 'Kotlin',
            'AI', 'ML', 'Deep Learning', 'NLP', 'Computer Vision',
            'Blockchain', 'Cryptocurrency', 'Web3', 'NFT', 'DeFi',
            'Remote Work', 'Freelancing', 'Side Hustle', 'Career',
            'Mindfulness', 'Motivation', 'Success', 'Habits', 'Goals',
            'Budget', 'Investing', 'Savings', 'Money Management',
            'Vegan', 'Keto', 'Mediterranean', 'Meal Prep', 'Quick Recipes',
            'Adventure', 'Solo Travel', 'Family Travel', 'Backpacking',
            'Photography Tips', 'Editing', 'Composition', 'Lighting',
            'Sustainability', 'Eco-Friendly', 'Zero Waste', 'Green Living',
        ];

        $name = fake()->randomElement($tags);

        return [
            'name' => $name,
            'slug' => '', // Let TagObserver generate unique slug
        ];
    }
}
