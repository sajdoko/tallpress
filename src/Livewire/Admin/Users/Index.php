<?php

namespace Sajdoko\TallPress\Livewire\Admin\Users;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;
use Sajdoko\TallPress\Livewire\Concerns\WithToast;
use Sajdoko\TallPress\Models\ActivityLog;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;
    use WithToast;

    public $search = '';

    public $roleFilter = '';

    public $editingUser = null;

    public $showForm = false;

    // Form fields
    public $name = '';

    public $email = '';

    public $password = '';

    public $password_confirmation = '';

    public $role = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'roleFilter' => ['except' => ''],
    ];

    public function mount()
    {
        // Check if user can manage users (admin only)
        $this->authorize('manageUsers', \Sajdoko\TallPress\Models\Post::class);
    }

    protected function rules()
    {
        $userId = $this->editingUser && $this->editingUser->exists ? $this->editingUser->id : 'NULL';

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$userId,
            'role' => 'required|in:'.implode(',', array_keys(config('tallpress.roles.available_roles', ['admin' => 'Administrator', 'editor' => 'Editor', 'author' => 'Author']))),
        ];

        // Password required only when creating new user
        if (! $this->editingUser) {
            $rules['password'] = 'required|string|min:8|confirmed';
        } else {
            // Password optional when editing, but must be confirmed if provided
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        return $rules;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function createUser()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function editUser($userId)
    {
        $userModel = config('tallpress.author_model', 'App\\Models\\User');
        $this->editingUser = $userModel::findOrFail($userId);

        $roleField = config('tallpress.roles.role_field', 'role');

        $this->name = $this->editingUser->name;
        $this->email = $this->editingUser->email;
        $this->role = $this->editingUser->{$roleField};
        $this->password = '';
        $this->password_confirmation = '';
        $this->showForm = true;
    }

    public function saveUser()
    {
        $this->validate();

        $roleField = config('tallpress.roles.role_field', 'role');
        $userModel = config('tallpress.author_model', 'App\\Models\\User');

        if ($this->editingUser) {
            // Find the user fresh from database and update
            $user = $userModel::findOrFail($this->editingUser->id);

            // Use direct assignment instead of mass assignment to bypass fillable
            $user->name = $this->name;
            $user->email = $this->email;
            $user->{$roleField} = $this->role;

            // Update password if provided
            if ($this->password) {
                $user->password = Hash::make($this->password);
            }

            $user->save();

            $action = 'updated user';
            $message = 'User updated successfully.';
        } else {
            // For new users, try mass assignment first, fallback to direct assignment
            try {
                $data = [
                    'name' => $this->name,
                    'email' => $this->email,
                    $roleField => $this->role,
                    'password' => Hash::make($this->password),
                ];
                $user = $userModel::create($data);
            } catch (\Illuminate\Database\Eloquent\MassAssignmentException $e) {
                // Fallback to direct assignment
                $user = new $userModel;
                $user->name = $this->name;
                $user->email = $this->email;
                $user->{$roleField} = $this->role;
                $user->password = Hash::make($this->password);
                $user->save();
            }

            $action = 'created user';
            $message = 'User created successfully.';
        }

        // Log activity
        if (tallpress_setting('activity_log_enabled', true)) {
            ActivityLog::log($action, null, auth()->user(), [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'role' => $user->{$roleField},
            ]);
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

    public function deleteUser($userId)
    {
        $userModel = config('tallpress.author_model', 'App\\Models\\User');
        $user = $userModel::findOrFail($userId);

        // Prevent user from deleting themselves
        if ($user->id === auth()->id()) {
            $this->toastError('You cannot delete your own account.');

            return;
        }

        $roleField = config('tallpress.roles.role_field', 'role');

        // Log activity before deletion
        if (tallpress_setting('activity_log_enabled', true)) {
            ActivityLog::log('deleted user', null, auth()->user(), [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'role' => $user->{$roleField},
            ]);
        }

        $user->delete();

        $this->toastSuccess('User deleted successfully.');
    }

    protected function resetForm()
    {
        $this->editingUser = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->role = config('tallpress.roles.default_role', 'author');
        $this->resetValidation();
    }

    public function render()
    {
        $userModel = config('tallpress.author_model', 'App\\Models\\User');
        $roleField = config('tallpress.roles.role_field', 'role');

        $query = $userModel::query()->withCount('posts');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->roleFilter) {
            $query->where($roleField, $this->roleFilter);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        $availableRoles = config('tallpress.roles.available_roles', [
            'admin' => 'Administrator',
            'editor' => 'Editor',
            'author' => 'Author',
        ]);

        return view('tallpress::admin.livewire.users.index', [
            'users' => $users,
            'availableRoles' => $availableRoles,
            'roleField' => $roleField,
        ])->layout('tallpress::admin.layout');
    }
}
