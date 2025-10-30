<?php

namespace Sajdoko\TallPress\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Sajdoko\TallPress\Database\Factories\CommentFactory;

/**
 * @property int $id
 * @property int $post_id
 * @property string $author_name
 * @property string $author_email
 * @property string $body
 * @property bool $approved
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Comment extends Model
{
    use HasFactory;

    protected $table = 'tallpress_comments';

    protected $fillable = [
        'post_id',
        'author_name',
        'author_email',
        'body',
        'approved',
    ];

    protected $casts = [
        'approved' => 'boolean',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return CommentFactory::new();
    }

    /**
     * Get the post that owns the comment.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    /**
     * Scope a query to only include approved comments.
     */
    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }

    /**
     * Scope a query to only include pending comments.
     */
    public function scopePending($query)
    {
        return $query->where('approved', false);
    }
}
