<div>
    <div class="page-header">
        @if($isEditMode)
            <h1 class="page-title">Edit Post</h1>
        @else
            <h1 class="page-title">Create Post</h1>
        @endif
        <a href="{{ route('tallpress.admin.posts.index') }}" class="btn btn-secondary">Back to Posts</a>
    </div>

    <form wire:submit.prevent="save">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Title & Slug -->
                <div class="card">
                    <div class="card-body space-y-4">
                        <div>
                            <label class="form-label">Title *</label>
                            <input type="text" wire:model="form.title" class="form-input" required>
                            @error('form.title') <span class="error">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="form-label">Slug</label>
                            <input type="text" wire:model="form.slug" class="form-input">
                            @error('form.slug') <span class="error">{{ $message }}</span> @enderror
                            <p class="text-sm text-gray-500">Leave empty to auto-generate from title</p>
                        </div>

                        <div>
                            <label class="form-label">Excerpt</label>
                            <textarea wire:model="form.excerpt" rows="3" class="form-input"></textarea>
                            @error('form.excerpt') <span class="error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Body -->
                <div class="card">
                    <div class="card-body">
                        <label class="form-label">Body *</label>
                        @if($editorType === 'rich')
                            <div wire:ignore
                                 x-data="quillEditor(@js($form->body ?? ''), '{{ route('tallpress.admin.media.upload') }}')"
                                 x-init="$watch('content', value => $wire.set('form.body', value, false))"
                                 class="quill-wrapper">
                                <div id="quill-editor" class="min-h-60"></div>
                            </div>
                        @else
                            <textarea wire:model="form.body" rows="20" class="form-input font-mono" required></textarea>
                            <p class="text-sm text-gray-500 mt-2">Supports Markdown formatting</p>
                        @endif
                        @error('form.body') <span class="alert-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- SEO Settings -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">SEO Settings</h3>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="form-label">SEO Title</label>
                            <input type="text" wire:model="form.seo_title" class="form-input" maxlength="60">
                            @error('form.seo_title') <span class="error">{{ $message }}</span> @enderror
                            <p class="text-sm text-gray-500">Leave empty to use post title. Recommended: 50-60 characters</p>
                        </div>

                        <div>
                            <label class="form-label">Meta Description</label>
                            <textarea wire:model="form.meta_description" rows="3" class="form-input" maxlength="160"></textarea>
                            @error('form.meta_description') <span class="error">{{ $message }}</span> @enderror
                            <p class="text-sm text-gray-500">Leave empty to use excerpt. Recommended: 150-160 characters</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Publish -->
                <div class="card">
                    <div class="card-header flex justify-between items-center">
                        <h3 class="card-title">Publish</h3>
                        @if($isEditMode)
                            <div class="flex space-x-2">
                                <a href="{{ route('tallpress.posts.show', ['post' => $form->post]) }}" target="_blank" class="btn btn-link text-sm">View</a>
                                <a href="{{ route('tallpress.admin.posts.revisions', ['post' => $form->post]) }}" class="btn btn-link text-sm">Revisions</a>
                            </div>
                        @endif
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="form-label">Status</label>
                            <select wire:model="form.status" class="form-select">
                                <option value="draft">Draft</option>
                                <option value="pending">Pending Review</option>
                                @can('publish', $form->post)
                                    <option value="published">Published</option>
                                @endcan
                            </select>
                            @error('form.status') <span class="error">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex space-x-2">
                            <button type="submit" class="btn btn-primary flex-1">
                                {{ $isEditMode ? 'Update' : 'Create' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Featured Image -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Featured Image</h3>
                    </div>
                    <div class="card-body space-y-4">
                        @if($form->existing_featured_image && !$form->remove_featured_image)
                            <div>
                                @php
                                    // Check if it's an external URL
                                    $isExternal = filter_var($form->existing_featured_image, FILTER_VALIDATE_URL) !== false;
                                    $imageUrl = $isExternal 
                                        ? $form->existing_featured_image 
                                        : \Illuminate\Support\Facades\Storage::disk(config('tallpress.storage_disk', 'public'))->url($form->existing_featured_image);
                                @endphp
                                <img src="{{ $imageUrl }}"
                                     alt="Featured Image" class="w-full rounded">
                                <button type="button" wire:click="removeFeaturedImage" class="btn btn-sm btn-danger mt-2">
                                    Remove Image
                                </button>
                            </div>
                        @else
                            <div>
                                <label class="form-label">Upload New Image</label>
                                <input type="file" wire:model="form.featured_image" accept="image/*" class="form-input">
                                @error('form.featured_image') <span class="error">{{ $message }}</span> @enderror

                                @if($form->featured_image)
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-600">Preview:</p>
                                        <img src="{{ $form->featured_image->temporaryUrl() }}" class="w-full rounded mt-1">
                                    </div>
                                @endif
                            </div>

                            <div class="text-center">
                                <span class="text-gray-500 text-sm">or</span>
                            </div>

                            <div>
                                <button type="button"
                                        @click="$dispatch('open-media-picker'); $wire.dispatch('setMediaTarget', { target: 'featured-image' })"
                                        class="btn btn-secondary w-full">
                                    📁 Choose from Library
                                </button>
                            </div>

                            <div class="text-center">
                                <span class="text-gray-500 text-sm">or</span>
                            </div>

                            <div>
                                <label class="form-label">External Image URL</label>
                                <input type="url" 
                                       wire:model="form.featured_image_external_url" 
                                       class="form-input" 
                                       placeholder="https://example.com/image.jpg">
                                @error('form.featured_image_external_url') <span class="error">{{ $message }}</span> @enderror
                                <p class="text-sm text-gray-500 mt-1">Enter a direct link to an image (jpg, jpeg, png, gif, webp, svg)</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Categories -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Categories</h3>
                    </div>
                    <div class="card-body">
                        <div class="space-y-2">
                            @foreach($categories as $category)
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model="form.selectedCategories" value="{{ $category->id }}" class="form-checkbox">
                                    <span class="ml-2">{{ $category->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('form.selectedCategories') <span class="error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Tags -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Tags</h3>
                    </div>
                    <div class="card-body">
                        <div class="space-y-2">
                            @foreach($tags as $tag)
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model="form.selectedTags" value="{{ $tag->id }}" class="form-checkbox">
                                    <span class="ml-2">{{ $tag->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('form.selectedTags') <span class="error">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Media Picker Modal -->
    @livewire('tallpress.admin.media.picker')
</div>
