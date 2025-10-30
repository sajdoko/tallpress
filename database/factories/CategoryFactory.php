<?php

namespace Sajdoko\TallPress\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Sajdoko\TallPress\Models\Category;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        // Realistic blog categories
        $categories = [
            'Technology', 'Web Development', 'Mobile Apps', 'Artificial Intelligence',
            'Machine Learning', 'Data Science', 'Cybersecurity', 'Cloud Computing',
            'Travel', 'Adventure Travel', 'Budget Travel', 'Luxury Travel',
            'Food & Cooking', 'Healthy Recipes', 'Restaurant Reviews', 'Baking',
            'Health & Wellness', 'Fitness', 'Mental Health', 'Nutrition',
            'Lifestyle', 'Home Decor', 'Personal Finance', 'Productivity',
            'Business', 'Entrepreneurship', 'Marketing', 'Startup',
            'Photography', 'Digital Photography', 'Photo Editing',
            'Sports', 'Outdoor Activities', 'Running', 'Yoga',
            'Entertainment', 'Movies', 'Music', 'Books & Reading',
            'Education', 'Online Learning', 'Career Development',
            'Fashion', 'Style Tips', 'Sustainable Fashion',
            'Gaming', 'Video Games', 'Board Games',
            'Gardening', 'Urban Gardening', 'Sustainable Living',
        ];

        $name = fake()->randomElement($categories);

        return [
            'name' => $name,
            'slug' => '', // Let CategoryObserver generate unique slug
            'description' => $this->getCategoryDescription($name),
        ];
    }

    /**
     * Get a realistic description for a category
     */
    protected function getCategoryDescription(string $category): string
    {
        $descriptions = [
            'Technology' => 'Explore the latest trends in technology, gadgets, and innovation.',
            'Web Development' => 'Tutorials, tips, and best practices for web developers.',
            'Mobile Apps' => 'Discover the latest mobile apps and development techniques.',
            'Travel' => 'Travel guides, tips, and inspiring destinations from around the world.',
            'Food & Cooking' => 'Delicious recipes, cooking tips, and culinary adventures.',
            'Health & Wellness' => 'Tips and advice for living a healthier, more balanced life.',
            'Lifestyle' => 'Inspiration and ideas for living your best life.',
            'Business' => 'Business strategies, insights, and entrepreneurship advice.',
            'Photography' => 'Photography tips, techniques, and inspiration for all skill levels.',
            'Sports' => 'Stay updated with sports news, tips, and training advice.',
        ];

        return $descriptions[$category] ?? 'Discover articles and insights about '.strtolower($category).'.';
    }
}
