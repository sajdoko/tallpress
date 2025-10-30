<?php

namespace Sajdoko\TallPress\Services;

use Sajdoko\TallPress\Models\Setting;

class SettingsService
{
    /**
     * Get a setting value with fallback to config
     */
    public function get(string $key, $default = null)
    {
        // Try to get from database first (with error handling for when table doesn't exist)
        try {
            $setting = Setting::get($key);

            if ($setting !== null) {
                return $setting;
            }
        } catch (\Exception $e) {
            // Table doesn't exist yet (migrations not run) - fall through to config
        }

        // Fallback to config
        $configKey = $this->convertKeyToConfigPath($key);

        return config($configKey, $default);
    }

    /**
     * Set a setting value
     */
    public function set(string $key, $value, string $type = 'string', string $group = 'general'): Setting
    {
        return Setting::set($key, $value, $type, $group);
    }

    /**
     * Update multiple settings at once
     */
    public function setMany(array $settings, string $group = 'general'): void
    {
        foreach ($settings as $key => $config) {
            $value = $config['value'] ?? $config;
            $type = $config['type'] ?? 'string';
            $settingGroup = $config['group'] ?? $group;

            $this->set($key, $value, $type, $settingGroup);
        }
    }

    /**
     * Get all settings organized by group
     */
    public function all(): array
    {
        $settings = Setting::all();
        $organized = [];

        foreach ($settings as $setting) {
            $organized[$setting->group][$setting->key] = $setting->value;
        }

        return $organized;
    }

    /**
     * Get settings for a specific group
     */
    public function getGroup(string $group): array
    {
        return Setting::getByGroup($group);
    }

    /**
     * Check if setting exists in database
     */
    public function has(string $key): bool
    {
        return Setting::has($key);
    }

    /**
     * Delete a setting
     */
    public function forget(string $key): bool
    {
        return Setting::forget($key);
    }

    /**
     * Initialize/reset default settings from config to database
     */
    public function initializeDefaults(): void
    {
        $defaults = [
            // General
            'per_page' => [
                'value' => config('tallpress.per_page', 15),
                'type' => 'integer',
                'group' => 'general',
            ],
            'route_prefix' => [
                'value' => config('tallpress.route_prefix', 'blog'),
                'type' => 'string',
                'group' => 'general',
            ],
            'admin_route_prefix' => [
                'value' => config('tallpress.admin_route_prefix', 'admin/blog'),
                'type' => 'string',
                'group' => 'general',
            ],

            // Blog
            'tallpress_title' => [
                'value' => config('blog.title', 'My Blog'),
                'type' => 'string',
                'group' => 'blog',
            ],
            'tallpress_description' => [
                'value' => config('blog.description', 'Welcome to my blog!'),
                'type' => 'string',
                'group' => 'blog',
            ],
            'tallpress_logo' => [
                'value' => config('blog.logo', null),
                'type' => 'string',
                'group' => 'blog',
            ],

            // Search
            'search_enabled' => [
                'value' => config('tallpress.search.enabled', true),
                'type' => 'boolean',
                'group' => 'search',
            ],

            // Comments
            'comments_enabled' => [
                'value' => config('tallpress.comments.enabled', true),
                'type' => 'boolean',
                'group' => 'comments',
            ],
            'comments_require_approval' => [
                'value' => config('tallpress.comments.require_approval', true),
                'type' => 'boolean',
                'group' => 'comments',
            ],

            // Images
            'images_max_size' => [
                'value' => config('tallpress.images.max_size', 2048),
                'type' => 'integer',
                'group' => 'images',
            ],
            'images_organize_by_date' => [
                'value' => config('tallpress.images.organize_by_date', true),
                'type' => 'boolean',
                'group' => 'images',
            ],
            'images_use_seo_filenames' => [
                'value' => config('tallpress.images.use_seo_filenames', true),
                'type' => 'boolean',
                'group' => 'images',
            ],

            // Editor
            'editor_type' => [
                'value' => config('tallpress.editor.type', 'rich'),
                'type' => 'string',
                'group' => 'editor',
            ],
            'editor_sanitize_html' => [
                'value' => config('tallpress.editor.sanitize_html', true),
                'type' => 'boolean',
                'group' => 'editor',
            ],

            // Revisions
            'revisions_enabled' => [
                'value' => config('tallpress.revisions.enabled', true),
                'type' => 'boolean',
                'group' => 'revisions',
            ],
            'revisions_keep' => [
                'value' => config('tallpress.revisions.keep_revisions', 10),
                'type' => 'integer',
                'group' => 'revisions',
            ],

            // Activity Log
            'activity_log_enabled' => [
                'value' => config('tallpress.activity_log.enabled', true),
                'type' => 'boolean',
                'group' => 'activity_log',
            ],
            'activity_log_keep_days' => [
                'value' => config('tallpress.activity_log.keep_days', 90),
                'type' => 'integer',
                'group' => 'activity_log',
            ],
        ];

        // Save all defaults to database (will create or update)
        foreach ($defaults as $key => $config) {
            $this->set($key, $config['value'], $config['type'], $config['group']);
        }
    }

    /**
     * Convert setting key to config path
     */
    protected function convertKeyToConfigPath(string $key): string
    {
        // Convert snake_case keys to dot notation config paths
        // e.g., 'per_page' -> 'tallpress.per_page'
        //       'comments_enabled' -> 'tallpress.comments.enabled'

        $parts = explode('_', $key);

        // Check if it maps to a nested config
        if (count($parts) >= 2) {
            $group = $parts[0];
            $setting = implode('_', array_slice($parts, 1));

            // Try nested path first
            $nestedPath = "tallpress.{$group}.{$setting}";
            if (config()->has($nestedPath)) {
                return $nestedPath;
            }
        }

        // Default to top-level config
        return "tallpress.{$key}";
    }
}
