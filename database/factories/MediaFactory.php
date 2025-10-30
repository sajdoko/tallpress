<?php

namespace Sajdoko\TallPress\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Sajdoko\TallPress\Models\Media;

class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        // Use Lorem Picsum for realistic placeholder images
        $imageId = fake()->numberBetween(1, 1000);
        $width = fake()->randomElement([800, 1024, 1200, 1600, 1920, 2000]);
        $height = fake()->randomElement([600, 768, 800, 900, 1080, 1200, 1500]);

        // Generate a realistic filename
        $topics = ['technology', 'nature', 'business', 'food', 'travel', 'lifestyle', 'architecture', 'abstract'];
        $topic = fake()->randomElement($topics);
        $filename = $topic.'-'.fake()->word().'-'.fake()->numberBetween(1000, 9999).'.jpg';

        $date = fake()->dateTimeBetween('-1 year', 'now');
        $yearMonth = $date->format('Y/m');
        $basePath = config('tallpress.images.path', 'tallpress/images');

        // Use Lorem Picsum URL for the image path
        $imageUrl = "https://picsum.photos/id/{$imageId}/{$width}/{$height}";

        return [
            'filename' => $filename,
            'path' => $imageUrl, // Store the Lorem Picsum URL directly
            'disk' => config('tallpress.storage_disk', 'public'),
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(50000, 2000000),
            'width' => $width,
            'height' => $height,
            'uploaded_by' => null, // Should be set when creating
            'alt_text' => $this->generateRealisticAltText($topic),
            'description' => fake()->optional()->sentence(),
            'created_at' => $date,
            'updated_at' => $date,
        ];
    }

    /**
     * Generate realistic alt text for accessibility
     */
    protected function generateRealisticAltText(string $topic): ?string
    {
        $altTexts = [
            'technology' => [
                'Modern laptop on a wooden desk with coffee',
                'Smartphone displaying code on screen',
                'Developer working on multiple monitors',
                'Abstract digital technology background',
            ],
            'nature' => [
                'Beautiful mountain landscape at sunset',
                'Forest path with sunlight filtering through trees',
                'Ocean waves crashing on rocky shore',
                'Colorful autumn leaves',
            ],
            'business' => [
                'Business team collaborating in modern office',
                'Professional handshake in business meeting',
                'Entrepreneur working on laptop in cafe',
                'Modern office workspace with plants',
            ],
            'food' => [
                'Delicious homemade meal on wooden table',
                'Fresh vegetables at farmer\'s market',
                'Beautifully plated gourmet dish',
                'Coffee and breakfast on sunny morning',
            ],
            'travel' => [
                'Ancient architecture in historic city',
                'Traveler with backpack overlooking valley',
                'Tropical beach with crystal clear water',
                'City skyline at dusk',
            ],
            'lifestyle' => [
                'Cozy home interior with natural light',
                'Person practicing yoga at sunrise',
                'Reading book with cup of tea',
                'Minimalist modern living room',
            ],
        ];

        $options = $altTexts[$topic] ?? ['Professional high-quality image'];

        return fake()->optional(0.8)->randomElement($options);
    }
}
