<?php

namespace Sajdoko\TallPress\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed get(string $key, $default = null)
 * @method static \Sajdoko\TallPress\Models\Setting set(string $key, $value, string $type = 'string', string $group = 'general')
 * @method static void setMany(array $settings, string $group = 'general')
 * @method static array all()
 * @method static array getGroup(string $group)
 * @method static bool has(string $key)
 * @method static bool forget(string $key)
 * @method static void initializeDefaults()
 *
 * @see \Sajdoko\TallPress\Services\SettingsService
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Sajdoko\TallPress\Services\SettingsService::class;
    }
}
