<?php

namespace Sajdoko\TallPress\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Sajdoko\TallPress\Models\Post;

class PostPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any posts.
     */
    public function viewAny($user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the post.
     */
    public function view($user, Post $post): bool
    {
        // Anyone can view published posts
        if ($post->isPublished()) {
            return true;
        }

        // Admins and editors can view any post
        if ($this->isAdminOrEditor($user)) {
            return true;
        }

        // Author can view their own drafts and pending posts
        return $post->author_id === $user->id;
    }

    /**
     * Determine whether the user can create posts.
     */
    public function create($user): bool
    {
        // Any authenticated user can create posts
        return true;
    }

    /**
     * Determine whether the user can update the post.
     */
    public function update($user, Post $post): bool
    {
        // Admins and editors can update any post
        if ($this->isAdminOrEditor($user)) {
            return true;
        }

        // Authors can update their own posts
        return $post->author_id === $user->id;
    }

    /**
     * Determine whether the user can delete the post.
     */
    public function delete($user, Post $post): bool
    {
        // Admins and editors can delete any post
        if ($this->isAdminOrEditor($user)) {
            return true;
        }

        // Authors can delete their own posts
        return $post->author_id === $user->id;
    }

    /**
     * Determine whether the user can restore the post.
     */
    public function restore($user, Post $post): bool
    {
        // Admins and editors can restore any post
        if ($this->isAdminOrEditor($user)) {
            return true;
        }

        // Authors can restore their own posts
        return $post->author_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the post.
     */
    public function forceDelete($user, Post $post): bool
    {
        // Only admins can permanently delete
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can publish the post.
     */
    public function publish($user, ?Post $post = null): bool
    {
        // Admins and editors can publish any post
        if ($this->isAdminOrEditor($user)) {
            return true;
        }

        // Authors can only set their own posts to pending
        return false;
    }

    /**
     * Determine whether the user can moderate comments.
     */
    public function moderateComments($user, ?Post $post = null): bool
    {
        return $this->isAdminOrEditor($user);
    }

    /**
     * Determine whether the user can manage media.
     */
    public function manageMedia($user, ?Post $post = null): bool
    {
        // All authenticated users can manage their own media
        return true;
    }

    /**
     * Determine whether the user can manage settings.
     */
    public function manageSettings($user, ?Post $post = null): bool
    {
        return $this->isAdminOrEditor($user);
    }

    /**
     * Determine whether the user can manage users.
     */
    public function manageUsers($user, ?Post $post = null): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Check if user is admin.
     */
    protected function isAdmin($user): bool
    {
        $roleField = config('tallpress.roles.role_field', 'role');

        return $user->{$roleField} === 'admin';
    }

    /**
     * Check if user is admin or editor.
     */
    protected function isAdminOrEditor($user): bool
    {
        $roleField = config('tallpress.roles.role_field', 'role');

        return in_array($user->{$roleField}, ['admin', 'editor']);
    }
}
