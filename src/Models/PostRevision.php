<?php

namespace Sajdoko\TallPress\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $post_id
 * @property int $user_id
 * @property string $title
 * @property string|null $excerpt
 * @property string $body
 * @property array|null $meta
 * @property \Illuminate\Support\Carbon $created_at
 */
class PostRevision extends Model
{
    public $timestamps = false;

    protected $table = 'tallpress_post_revisions';

    protected $fillable = [
        'post_id',
        'user_id',
        'title',
        'excerpt',
        'body',
        'meta',
        'created_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the post that owns the revision.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    /**
     * Get the user who created the revision.
     */
    public function user(): BelongsTo
    {
        $authorModel = config('tallpress.author_model', 'App\\Models\\User');

        return $this->belongsTo($authorModel, 'user_id');
    }
}
