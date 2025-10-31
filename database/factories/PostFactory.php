<?php

namespace Sajdoko\TallPress\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Sajdoko\TallPress\Models\Post;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = $this->generateRealisticTitle();

        // Use Lorem Picsum for featured images (free, no API key required)
        // Images are 1200x630 (good for blog featured images)
        $imageId = fake()->numberBetween(1, 1000);
        $featuredImage = "https://picsum.photos/id/{$imageId}/1200/630";

        return [
            'title' => $title,
            'slug' => '', // Let the PostObserver generate unique slug
            'excerpt' => $this->generateRealisticExcerpt($title),
            'body' => $this->generateMarkdownContent($title),
            'status' => fake()->randomElement(['draft', 'pending', 'published']),
            'published_at' => fake()->boolean(70) ? fake()->dateTimeBetween('-6 months', 'now') : null,
            'author_id' => null, // Should be set when creating
            'featured_image' => $featuredImage,
            'meta' => [
                'views' => 0, // Start with 0 views for clean testing
                'read_time' => fake()->numberBetween(1, 15),
            ],
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function withRandomViews(): static
    {
        return $this->state(function (array $attributes) {
            $meta = $attributes['meta'] ?? [];
            $meta['views'] = fake()->numberBetween(100, 10000);

            return ['meta' => $meta];
        });
    }

    /**
     * Generate a realistic blog post title
     */
    protected function generateRealisticTitle(): string
    {
        $titleTemplates = [
            // How-to articles
            'How to [action] in [timeframe]',
            'The Complete Guide to [topic]',
            'A Beginner\'s Guide to [topic]',
            '[number] Ways to [action]',
            '[number] Tips for [topic]',

            // List articles
            'Top [number] [topic] You Need to Know',
            '[number] Essential [topic] for [audience]',
            '[number] Best [topic] in [year]',
            'The Ultimate List of [topic]',

            // Problem-solving
            'How to Fix [problem]',
            'Solving [problem]: A Step-by-Step Guide',
            'Why [problem] Happens and How to Prevent It',

            // Comparison articles
            '[topic] vs [topic2]: Which is Better?',
            'Comparing [topic] and [topic2]',

            // Opinion pieces
            'Why [topic] Matters in [year]',
            'The Future of [topic]',
            'Is [topic] Worth It?',

            // Tutorial articles
            'Building [project] with [technology]',
            'Creating [project]: A Tutorial',
            'Mastering [skill] in [timeframe]',
        ];

        $topics = [
            'Web Development', 'React', 'Laravel', 'JavaScript', 'Python',
            'Machine Learning', 'Data Science', 'Cloud Computing', 'DevOps',
            'Remote Work', 'Productivity', 'Time Management', 'Digital Marketing',
            'SEO', 'Content Writing', 'Photography', 'Travel Planning',
            'Healthy Eating', 'Fitness', 'Meditation', 'Personal Finance',
            'Sustainable Living', 'Home Organization', 'Career Growth',
            'API Development', 'Mobile Apps', 'UI/UX Design', 'Cybersecurity',
            'Blockchain', 'Microservices', 'Testing', 'CI/CD', 'Kubernetes',
        ];

        $actions = [
            'Master', 'Learn', 'Improve', 'Boost', 'Optimize', 'Build',
            'Create', 'Design', 'Develop', 'Start', 'Launch', 'Scale',
            'Automate', 'Enhance', 'Streamline', 'Implement',
        ];

        $template = fake()->randomElement($titleTemplates);
        $title = $template;

        // Get two different topics for comparison articles
        $topicsList = $topics;
        $topic1 = fake()->randomElement($topicsList);
        $topic2 = fake()->randomElement(array_diff($topicsList, [$topic1]));

        // Replace placeholders
        $title = str_replace('[number]', (string) fake()->numberBetween(3, 15), $title);
        $title = str_replace('[topic2]', $topic2, $title);
        $title = str_replace('[topic]', $topic1, $title);
        $title = str_replace('[action]', fake()->randomElement($actions), $title);
        $title = str_replace('[timeframe]', fake()->randomElement(['2024', '2025', '30 Days', 'a Week', 'a Month', 'One Year']), $title);
        $title = str_replace('[year]', '2025', $title);
        $title = str_replace('[audience]', fake()->randomElement(['Beginners', 'Developers', 'Professionals', 'Everyone', 'Teams', 'Startups']), $title);
        $title = str_replace('[problem]', fake()->randomElement(['Common Errors', 'Performance Issues', 'Security Vulnerabilities', 'Scaling Problems']), $title);
        $title = str_replace('[technology]', fake()->randomElement(['React', 'Laravel', 'Node.js', 'Vue.js', 'Next.js', 'Django']), $title);
        $title = str_replace('[project]', fake()->randomElement(['a Blog', 'a Portfolio', 'an E-commerce Site', 'a Dashboard', 'a SaaS App', 'a Mobile App']), $title);
        $title = str_replace('[skill]', fake()->randomElement(['CSS Grid', 'REST APIs', 'Docker', 'Git', 'GraphQL', 'Webpack']), $title);

        return $title;
    }

    /**
     * Generate a realistic excerpt based on the title
     */
    protected function generateRealisticExcerpt(string $title): string
    {
        $excerptTemplates = [
            'Discover everything you need to know about this topic in our comprehensive guide.',
            'Learn the essential techniques and best practices that will help you succeed.',
            'In this article, we\'ll explore the key concepts and practical applications you can use today.',
            'This detailed guide will walk you through everything step by step.',
            'Get actionable insights and proven strategies in this in-depth article.',
            'We\'ll cover the fundamentals and advanced techniques to help you master this topic.',
            'Find out how to take your skills to the next level with these expert tips.',
            'Everything you need to get started, explained in simple terms.',
        ];

        return fake()->randomElement($excerptTemplates);
    }

    protected function generateMarkdownContent(string $title): string
    {
        // Generate topic-relevant content sections
        $sections = fake()->numberBetween(3, 6);
        $content = '';

        // Introduction
        $content .= "## Introduction\n\n";
        $content .= "In today's fast-paced digital world, understanding this topic has become increasingly important. ";
        $content .= "Whether you're just getting started or looking to deepen your knowledge, this guide will provide you with practical insights and actionable strategies.\n\n";
        $content .= "Let's dive in and explore everything you need to know.\n\n";

        // Main content sections
        for ($i = 1; $i <= $sections; $i++) {
            $sectionTitles = [
                'Getting Started',
                'Key Concepts',
                'Best Practices',
                'Common Pitfalls to Avoid',
                'Advanced Techniques',
                'Real-World Applications',
                'Tools and Resources',
                'Tips from Experts',
                'Step-by-Step Guide',
                'Performance Optimization',
            ];

            $content .= '## '.fake()->randomElement($sectionTitles)."\n\n";

            // Add 2-3 paragraphs per section
            $paragraphs = fake()->numberBetween(2, 3);
            for ($p = 0; $p < $paragraphs; $p++) {
                $content .= fake()->paragraph(fake()->numberBetween(4, 6))."\n\n";
            }

            // Sometimes add a list
            if (fake()->boolean(60)) {
                $content .= "### Key Points\n\n";
                $listItems = fake()->numberBetween(3, 5);
                for ($l = 0; $l < $listItems; $l++) {
                    $content .= '- '.fake()->sentence()."\n";
                }
                $content .= "\n";
            }

            // Sometimes add a code block or quote
            if (fake()->boolean(30)) {
                if (fake()->boolean(50)) {
                    $content .= "```php\n";
                    $content .= "// Example code\n";
                    $content .= "function example() {\n";
                    $content .= "    return 'This is a sample code block';\n";
                    $content .= "}\n";
                    $content .= "```\n\n";
                } else {
                    $content .= '> '.fake()->sentence()."\n\n";
                }
            }
        }

        // Conclusion
        $content .= "## Conclusion\n\n";
        $content .= "We've covered a lot of ground in this article. By applying these principles and techniques, you'll be well on your way to achieving your goals. ";
        $content .= "Remember, practice makes perfect, so don't be afraid to experiment and learn from your experiences.\n\n";
        $content .= "What are your thoughts on this topic? Feel free to share your experiences in the comments below!\n";

        return $content;
    }
}
