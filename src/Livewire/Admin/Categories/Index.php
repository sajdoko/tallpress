<?php

namespace Sajdoko\TallPress\Livewire\Admin\Categories;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;
use Sajdoko\TallPress\Livewire\Concerns\WithToast;
use Sajdoko\TallPress\Models\ActivityLog;
use Sajdoko\TallPress\Models\Category;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;
    use WithToast;

    public $search = '';

    public $editingCategory = null;

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
        $categoryId = $this->editingCategory && $this->editingCategory->exists ? $this->editingCategory->id : 'NULL';

        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:tallpress_categories,slug,'.$categoryId,
            'description' => 'nullable|string',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function createCategory()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function editCategory($categoryId)
    {
        $this->editingCategory = Category::findOrFail($categoryId);
        $this->name = $this->editingCategory->name;
        $this->slug = $this->editingCategory->slug;
        $this->description = $this->editingCategory->description ?? '';
        $this->showForm = true;
    }

    public function saveCategory()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description,
        ];

        if ($this->slug) {
            $data['slug'] = $this->slug;
        }

        if ($this->editingCategory) {
            $this->editingCategory->update($data);
            $action = 'updated category';
            $message = 'Category updated successfully.';
            $category = $this->editingCategory;
        } else {
            $category = Category::create($data);
            $action = 'created category';
            $message = 'Category created successfully.';
        }

        // Log activity
        if (tallpress_setting('activity_log_enabled', true)) {
            ActivityLog::log($action, $category, auth()->user());
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

    public function deleteCategory($categoryId)
    {
        $category = Category::findOrFail($categoryId);

        // Log activity before deletion
        if (tallpress_setting('activity_log_enabled', true)) {
            ActivityLog::log('deleted category', $category, auth()->user(), [
                'name' => $category->name,
            ]);
        }

        $category->delete();

        $this->toastSuccess('Category deleted successfully.');
    }

    protected function resetForm()
    {
        $this->editingCategory = null;
        $this->name = '';
        $this->slug = '';
        $this->description = '';
        $this->resetValidation();
    }

    public function render()
    {
        $query = Category::withCount('posts');

        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('description', 'like', '%'.$this->search.'%');
        }

        $categories = $query->orderBy('name')->paginate(15);

        return view('tallpress::admin.livewire.categories.index', [
            'categories' => $categories,
        ])->layout('tallpress::admin.layout');
    }
}
