<?php

namespace Sajdoko\TallPress\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Sajdoko\TallPress\Database\Factories\TagFactory;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Tag extends Model
{
    use HasFactory;

    protected $table = 'tallpress_tags';

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return TagFactory::new();
    }

    /**
     * Get the posts for the tag.
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'tallpress_post_tag', 'tag_id', 'post_id');
    }

    /**
     * Get the published posts for the tag.
     */
    public function publishedPosts(): BelongsToMany
    {
        return $this->posts()->published();
    }
}
