<?php

namespace Sajdoko\TallPress\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $key
 * @property mixed $value
 * @property string $type
 * @property string $group
 * @property bool $autoload
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Setting extends Model
{
    use HasFactory;

    protected $table = 'tallpress_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'autoload',
    ];

    protected $casts = [
        'autoload' => 'boolean',
    ];

    /**
     * Get the setting value with proper type casting
     */
    public function getValueAttribute($value)
    {
        return $this->castValue($value, $this->type);
    }

    /**
     * Set the setting value with proper type casting
     */
    public function setValueAttribute($value)
    {
        $this->attributes['value'] = $this->prepareValue($value, $this->type);
    }

    /**
     * Cast value to appropriate type
     */
    protected function castValue($value, $type)
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'array', 'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Prepare value for storage
     */
    protected function prepareValue($value, $type)
    {
        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'array', 'json' => json_encode($value),
            default => (string) $value,
        };
    }

    /**
     * Get setting by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting value
     */
    public static function set(string $key, $value, string $type = 'string', string $group = 'general'): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
            ]
        );
    }

    /**
     * Check if setting exists
     */
    public static function has(string $key): bool
    {
        return static::where('key', $key)->exists();
    }

    /**
     * Delete setting
     */
    public static function forget(string $key): bool
    {
        return static::where('key', $key)->delete();
    }

    /**
     * Get all settings by group
     */
    public static function getByGroup(string $group): array
    {
        return static::where('group', $group)
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Get all autoload settings
     */
    public static function getAutoload(): array
    {
        return static::where('autoload', true)
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Sajdoko\TallPress\Database\Factories\SettingFactory::new();
    }
}
