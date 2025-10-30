<?php

namespace Sajdoko\TallPress\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Sajdoko\TallPress\Models\Media;

class MediaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any media.
     */
    public function viewAny($user): bool
    {
        // All authenticated users can view media list
        return true;
    }

    /**
     * Determine whether the user can view the media.
     */
    public function view($user, Media $media): bool
    {
        // All authenticated users can view media
        return true;
    }

    /**
     * Determine whether the user can create media.
     */
    public function create($user): bool
    {
        // All authenticated users can upload media
        return true;
    }

    /**
     * Determine whether the user can update the media.
     */
    public function update($user, Media $media): bool
    {
        // Admins and editors can update any media
        if ($this->isAdminOrEditor($user)) {
            return true;
        }

        // Users can update their own media
        return $media->uploaded_by === $user->id;
    }

    /**
     * Determine whether the user can delete the media.
     */
    public function delete($user, Media $media): bool
    {
        // Admins and editors can delete any media
        if ($this->isAdminOrEditor($user)) {
            return true;
        }

        // Users can delete their own media
        return $media->uploaded_by === $user->id;
    }

    /**
     * Determine whether the user can restore the media.
     */
    public function restore($user, Media $media): bool
    {
        // Admins and editors can restore any media
        if ($this->isAdminOrEditor($user)) {
            return true;
        }

        // Users can restore their own media
        return $media->uploaded_by === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the media.
     */
    public function forceDelete($user, Media $media): bool
    {
        // Only admins can permanently delete
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
