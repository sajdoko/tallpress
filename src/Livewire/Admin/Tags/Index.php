<?php

namespace Sajdoko\TallPress\Livewire\Admin\Tags;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;
use Sajdoko\TallPress\Livewire\Concerns\WithToast;
use Sajdoko\TallPress\Models\ActivityLog;
use Sajdoko\TallPress\Models\Tag;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;
    use WithToast;

    public $search = '';

    public $editingTag = null;

    public $showForm = false;

    // Form fields
    public $name = '';

    public $slug = '';

    public $description = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    protected function rules()
    {
        $tagId = $this->editingTag && $this->editingTag->exists ? $this->editingTag->id : 'NULL';

        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:tallpress_tags,slug,'.$tagId,
            'description' => 'nullable|string',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function createTag()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function editTag($tagId)
    {
        $this->editingTag = Tag::findOrFail($tagId);
        $this->name = $this->editingTag->name;
        $this->slug = $this->editingTag->slug;
        $this->description = $this->editingTag->description ?? '';
        $this->showForm = true;
    }

    public function saveTag()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description,
        ];

        if ($this->slug) {
            $data['slug'] = $this->slug;
        }

        if ($this->editingTag) {
            $this->editingTag->update($data);
            $action = 'updated tag';
            $message = 'Tag updated successfully.';
            $tag = $this->editingTag;
        } else {
            $tag = Tag::create($data);
            $action = 'created tag';
            $message = 'Tag created successfully.';
        }

        // Log activity
        if (tallpress_setting('activity_log_enabled', true)) {
            ActivityLog::log($action, $tag, auth()->user());
        }

        $this->resetForm();
        $this->showForm = false;
        $this->toastSuccess($message);
    }

    public function cancelForm()
    {
        $this->resetForm();
        $this->showForm = false;
    }

    public function deleteTag($tagId)
    {
        $tag = Tag::findOrFail($tagId);

        // Log activity before deletion
        if (tallpress_setting('activity_log_enabled', true)) {
            ActivityLog::log('deleted tag', $tag, auth()->user(), [
                'name' => $tag->name,
            ]);
        }

        $tag->delete();

        $this->toastSuccess('Tag deleted successfully.');
    }

    protected function resetForm()
    {
        $this->editingTag = null;
        $this->name = '';
        $this->slug = '';
        $this->description = '';
        $this->resetValidation();
    }

    public function render()
    {
        $query = Tag::withCount('posts');

        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('description', 'like', '%'.$this->search.'%');
        }

        $tags = $query->orderBy('name')->paginate(15);

        return view('tallpress::admin.livewire.tags.index', [
            'tags' => $tags,
        ])->layout('tallpress::admin.layout');
    }
}
