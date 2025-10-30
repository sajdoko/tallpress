<?php

use Sajdoko\TallPress\Services\SettingsService;

if (! function_exists('tallpress_setting')) {
    /**
     * Get a tallpress setting value with fallback to config
     *
     * @param  mixed  $default
     * @return mixed
     */
    function tallpress_setting(string $key, $default = null)
    {
        try {
            $settingsService = app(SettingsService::class);

            return $settingsService->get($key, $default);
        } catch (\Exception $e) {
            // Fallback to config if service is not available (e.g., during migrations)
            $configKey = str_replace('_', '.', $key);
            if (! str_contains($configKey, 'tallpress.')) {
                $configKey = "tallpress.{$configKey}";
            }

            return config($configKey, $default);
        }
    }
}
