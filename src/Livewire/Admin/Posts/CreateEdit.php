<?php

namespace Sajdoko\TallPress\Livewire\Admin\Posts;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithFileUploads;
use Sajdoko\TallPress\Livewire\Concerns\WithToast;
use Sajdoko\TallPress\Livewire\Forms\PostForm;
use Sajdoko\TallPress\Models\Category;
use Sajdoko\TallPress\Models\Post;
use Sajdoko\TallPress\Models\Tag;

class CreateEdit extends Component
{
    use AuthorizesRequests, WithFileUploads, WithToast;

    public PostForm $form;

    public $isEditMode = false;

    public $editorType = 'rich';

    protected $listeners = ['mediaSelectedForFeatured' => 'handleMediaSelected'];

    public function mount(?Post $post)
    {
        $this->editorType = tallpress_setting('editor_type', 'rich');

        // If $post is a Post model instance (from route model binding for edit)
        if ($post && $post->exists) {
            $this->isEditMode = true;
            $this->authorize('update', $post);
            $this->form->setPost($post);
        } else {
            // Create mode - no post parameter or null
            $this->authorize('create', Post::class);
            $this->form->post = new Post;
        }
    }

    public function save()
    {
        if ($this->isEditMode) {
            $this->authorize('update', $this->form->post);
        } else {
            $this->authorize('create', Post::class);
        }

        // Additional authorization for publishing
        if ($this->form->status === 'published') {
            $this->authorize('publish', $this->form->post);
        }

        if ($this->isEditMode) {
            $this->form->update();
        } else {
            $this->form->store();
        }

        session()->flash('success', $this->isEditMode ? 'Post updated successfully.' : 'Post created successfully.');

        $this->redirectRoute('tallpress.admin.posts.edit', ['post' => $this->form->post->id]);
    }

    public function removeFeaturedImage()
    {
        $this->form->removeFeaturedImage();
    }

    public function handleMediaSelected($mediaData)
    {
        // Set the featured image URL from media library
        // Store just the path part for the database
        $url = $mediaData['url'];
        $path = parse_url($url, PHP_URL_PATH);

        // Remove /storage/ prefix if present
        $path = preg_replace('#^/storage/#', '', $path);

        $this->form->featured_image_from_library = $path;
        $this->form->existing_featured_image = $path;
        $this->form->remove_featured_image = false;
    }

    public function getTitle()
    {
        return $this->isEditMode ? 'Edit Post' : 'Create Post';
    }

    public function render()
    {
        $categories = Category::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();

        return view('tallpress::admin.livewire.posts.create-edit', [
            'categories' => $categories,
            'tags' => $tags,
        ])->layout('tallpress::admin.layout')->title($this->getTitle());
    }
}
