<?php

namespace Sajdoko\TallPress\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $log_name
 * @property string $description
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property string|null $causer_type
 * @property int|null $causer_id
 * @property array|null $properties
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ActivityLog extends Model
{
    protected $table = 'tallpress_activity_logs';

    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * Get the subject of the activity.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the causer of the activity.
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Log an activity.
     *
     * @param  \Illuminate\Database\Eloquent\Model|null  $causer
     */
    public static function log(string $description, ?Model $subject = null, $causer = null, array $properties = []): self
    {
        $causerId = $causer?->id ?? null;

        return static::create([
            'log_name' => 'tallpress',
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'causer_type' => $causer ? get_class($causer) : null,
            'causer_id' => $causerId,
            'properties' => $properties,
        ]);
    }
}
