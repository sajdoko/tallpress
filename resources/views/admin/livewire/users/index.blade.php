<div>
    <div class="page-header">
        <h1 class="page-title">Users</h1>
        <button wire:click="createUser" class="btn btn-primary">New User</button>
    </div>

    <!-- Search & Filters -->
    <div class="card mb-6">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <input type="text" wire:model.live.debounce.500ms="search" placeholder="Search users by name or email..." class="form-input">
                </div>
                <div>
                    <select wire:model.live="roleFilter" class="form-input">
                        <option value="">All Roles</option>
                        @foreach($availableRoles as $roleKey => $roleLabel)
                            <option value="{{ $roleKey }}">{{ $roleLabel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Modal -->
    @if($showForm)
        <div class="card mb-6 bg-blue-50">
            <div class="card-header flex justify-between items-center">
                <h3 class="card-title">{{ $editingUser ? 'Edit User' : 'New User' }}</h3>
                <button wire:click="cancelForm" class="btn btn-sm btn-secondary">Cancel</button>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="saveUser">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Name *</label>
                            <input type="text" wire:model="name" class="form-input" required>
                            @error('name') <span class="error">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="form-label">Email *</label>
                            <input type="email" wire:model="email" class="form-input" required>
                            @error('email') <span class="error">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="form-label">Password {{ $editingUser ? '(leave blank to keep current)' : '*' }}</label>
                            <input type="password" wire:model="password" class="form-input" {{ $editingUser ? '' : 'required' }}>
                            @error('password') <span class="error">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="form-label">Confirm Password {{ $editingUser ? '' : '*' }}</label>
                            <input type="password" wire:model="password_confirmation" class="form-input" {{ $editingUser ? '' : 'required' }}>
                            @error('password_confirmation') <span class="error">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Role *</label>
                            <select wire:model="role" class="form-input" required>
                                <option value="">Select a role</option>
                                @foreach($availableRoles as $roleKey => $roleLabel)
                                    <option value="{{ $roleKey }}">{{ $roleLabel }}</option>
                                @endforeach
                            </select>
                            @error('role') <span class="error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            {{ $editingUser ? 'Update User' : 'Create User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Users Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Posts Count</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="font-medium">
                                {{ $user->name }}
                                @if($user->id === auth()->id())
                                    <span class="badge badge-info ml-2">You</span>
                                @endif
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge badge-{{ $user->{$roleField} === 'admin' ? 'danger' : ($user->{$roleField} === 'editor' ? 'warning' : 'success') }}">
                                    {{ ucfirst($user->{$roleField}) }}
                                </span>
                            </td>
                            <td>{{ $user->posts_count }}</td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="flex space-x-2">
                                    <button wire:click="editUser({{ $user->id }})" class="btn btn-xs btn-secondary">
                                        Edit
                                    </button>
                                    @if($user->id !== auth()->id())
                                        <button wire:click="deleteUser({{ $user->id }})"
                                                wire:confirm="Are you sure you want to delete this user? This action cannot be undone."
                                                class="btn btn-xs btn-danger">
                                            Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-gray-500">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="card-footer">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
