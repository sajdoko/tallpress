<?php

namespace Sajdoko\TallPress\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Sajdoko\TallPress\Database\Factories\CategoryFactory;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Category extends Model
{
    use HasFactory;

    protected $table = 'tallpress_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return CategoryFactory::new();
    }

    /**
     * Get the posts for the category.
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'tallpress_post_category', 'category_id', 'post_id');
    }

    /**
     * Get the published posts for the category.
     */
    public function publishedPosts(): BelongsToMany
    {
        return $this->posts()->published();
    }
}
