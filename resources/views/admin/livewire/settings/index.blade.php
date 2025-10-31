<div>
    <div class="page-header">
        <h1 class="page-title">Blog Settings</h1>
        <div class="flex items-center gap-2">
            <!-- Auto-save indicator -->
            <div class="text-sm text-gray-600">
                <span wire:loading wire:target="updated" class="flex items-center gap-1">
                    <svg class="animate-spin h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Saving...</span>
                </span>
                @if($lastSaved)
                <span wire:loading.remove wire:target="updated" class="flex items-center gap-1">
                    <svg class="h-4 w-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Saved at {{ $lastSaved }}</span>
                </span>
                @endif
            </div>

            <button wire:click="resetToDefaults"
                    class="btn btn-secondary"
                    wire:confirm="Are you sure you want to reset all settings to defaults? This action cannot be undone."
                    >
                Reset to Defaults
            </button>
            <button wire:click="saveSettings" class="btn btn-primary">
                Save All Settings
            </button>
        </div>
    </div>

    <div class="card">
        <!-- Tabs Navigation -->
        <div class="border-b border-gray-200">
            <nav class="flex space-x-4 px-6" aria-label="Tabs">
                <button wire:click="setActiveTab('general')"
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'general' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    General
                </button>
                <button wire:click="setActiveTab('tallpress')"
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'tallpress' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Blog
                </button>
                <button wire:click="setActiveTab('search')"
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'search' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Search
                </button>
                <button wire:click="setActiveTab('comments')"
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'comments' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Comments
                </button>
                <button wire:click="setActiveTab('images')"
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'images' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Images
                </button>
                <button wire:click="setActiveTab('editor')"
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'editor' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Editor
                </button>
                <button wire:click="setActiveTab('revisions')"
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'revisions' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Revisions
                </button>
                <button wire:click="setActiveTab('activity_log')"
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'activity_log' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Activity Log
                </button>
                <button wire:click="setActiveTab('social_share')"
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'social_share' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Social Sharing
                </button>
            </nav>
        </div>

        <div class="card-body">
            <!-- General Settings Tab -->
            @if($activeTab === 'general')
            <div class="space-y-4">
                <h3 class="text-lg font-semibold mb-4">General Settings</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Posts Per Page</label>
                        <div class="relative">
                            <input type="number" wire:model.blur="per_page"
                                   class="form-input"
                                   min="1" max="100"
                                   wire:dirty.class="border-yellow-400">
                            <div wire:dirty wire:target="per_page"
                                 class="absolute right-2 top-2 text-yellow-600 text-xs font-medium">
                                Unsaved
                            </div>
                        </div>
                        @error('per_page') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        <p class="text-sm text-gray-500 mt-1">Number of posts to display per page</p>
                    </div>

                    <div>
                        <label class="form-label">Blog Route Prefix</label>
                        <div class="relative">
                            <input type="text" wire:model.blur="route_prefix"
                                   class="form-input"
                                   wire:dirty.class="border-yellow-400">
                            <div wire:dirty wire:target="route_prefix"
                                 class="absolute right-2 top-2 text-yellow-600 text-xs font-medium">
                                Unsaved
                            </div>
                        </div>
                        @error('route_prefix') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        <p class="text-sm text-gray-500 mt-1">URL prefix for blog routes (e.g., "blog" for /blog)</p>
                    </div>

                    <div>
                        <label class="form-label">Admin Route Prefix</label>
                        <div class="relative">
                            <input type="text" wire:model.blur="admin_route_prefix"
                                   class="form-input"
                                   wire:dirty.class="border-yellow-400">
                            <div wire:dirty wire:target="admin_route_prefix"
                                 class="absolute right-2 top-2 text-yellow-600 text-xs font-medium">
                                Unsaved
                            </div>
                        </div>
                        @error('admin_route_prefix') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        <p class="text-sm text-gray-500 mt-1">URL prefix for admin routes (e.g., "admin/blog")</p>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded p-4 mt-6">
                    <p class="text-sm text-blue-900">
                        <svg class="inline h-4 w-4 text-blue-600 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <strong>Note:</strong> When you change route prefixes, the route cache is automatically cleared and changes take effect immediately.
                    </p>
                </div>
            </div>
            @endif

            <!-- Blog Settings Tab -->
            @if($activeTab === 'tallpress')
            <div class="space-y-4">
                <h3 class="text-lg font-semibold mb-4">Blog Settings</h3>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="form-label">Blog Title</label>
                        <div class="relative">
                            <input type="text" wire:model.blur="tallpress_title"
                                   class="form-input"
                                   placeholder="My Awesome Blog"
                                   wire:dirty.class="border-yellow-400">
                            <div wire:dirty wire:target="tallpress_title"
                                 class="absolute right-2 top-2 text-yellow-600 text-xs font-medium">
                                Unsaved
                            </div>
                        </div>
                        @error('tallpress_title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        <p class="text-sm text-gray-500 mt-1">The title of your blog that appears in the header and meta tags</p>
                    </div>

                    <div>
                        <label class="form-label">Blog Description</label>
                        <div class="relative">
                            <textarea wire:model.blur="tallpress_description"
                                      rows="3"
                                      class="form-textarea"
                                      placeholder="A modern blog platform built with Laravel"
                                      wire:dirty.class="border-yellow-400"></textarea>
                            <div wire:dirty wire:target="tallpress_description"
                                 class="absolute right-2 top-2 text-yellow-600 text-xs font-medium">
                                Unsaved
                            </div>
                        </div>
                        @error('tallpress_description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        <p class="text-sm text-gray-500 mt-1">A brief description of your blog (used in meta tags and footer)</p>
                    </div>

                    <div>
                        <label class="form-label">Blog Logo Path</label>
                        <div class="relative">
                            <input type="text" wire:model.blur="tallpress_logo"
                                   class="form-input"
                                   placeholder="images/logo.png"
                                   wire:dirty.class="border-yellow-400">
                            <div wire:dirty wire:target="tallpress_logo"
                                 class="absolute right-2 top-2 text-yellow-600 text-xs font-medium">
                                Unsaved
                            </div>
                        </div>
                        @error('tallpress_logo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        <p class="text-sm text-gray-500 mt-1">Path to your blog logo image (relative to public directory). Leave empty to use default icon.</p>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded p-4 mt-6">
                    <p class="text-sm text-blue-900">
                        <svg class="inline h-4 w-4 text-blue-600 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <strong>Tip:</strong> These settings control how your blog appears to visitors. Upload your logo to the media library and copy its path here.
                    </p>
                </div>
            </div>
            @endif

            <!-- Search Settings Tab -->
            @if($activeTab === 'search')
            <div class="space-y-4">
                <h3 class="text-lg font-semibold mb-4">Search Settings</h3>

                <div class="relative">
                    <label class="flex items-center">
                        <input type="checkbox" wire:model.live="search_enabled" class="form-checkbox">
                        <span class="ml-2">Enable Search Functionality</span>
                        <span wire:dirty wire:target="search_enabled"
                              class="ml-2 text-yellow-600 text-xs font-medium">
                            (Unsaved)
                        </span>
                    </label>
                    <p class="text-sm text-gray-500 mt-1 ml-6">Allow users to search for posts</p>
                </div>
            </div>
            @endif

            <!-- Comments Settings Tab -->
            @if($activeTab === 'comments')
            <div class="space-y-4">
                <h3 class="text-lg font-semibold mb-4">Comments Settings</h3>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" wire:model.live="comments_enabled" class="form-checkbox">
                        <span class="ml-2">Enable Comments</span>
                        <span wire:dirty wire:target="comments_enabled"
                              class="ml-2 text-yellow-600 text-xs font-medium">
                            (Unsaved)
                        </span>
                    </label>
                    <p class="text-sm text-gray-500 mt-1 ml-6">Allow users to post comments on blog posts</p>
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" wire:model.live="comments_require_approval" class="form-checkbox">
                        <span class="ml-2">Require Comment Approval</span>
                        <span wire:dirty wire:target="comments_require_approval"
                              class="ml-2 text-yellow-600 text-xs font-medium">
                            (Unsaved)
                        </span>
                    </label>
                    <p class="text-sm text-gray-500 mt-1 ml-6">Comments must be approved by an admin before appearing publicly</p>
                </div>
            </div>
            @endif

            <!-- Images Settings Tab -->
            @if($activeTab === 'images')
            <div class="space-y-4">
                <h3 class="text-lg font-semibold mb-4">Image Settings</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Maximum Image Size (KB)</label>
                        <div class="relative">
                            <input type="number" wire:model.blur="images_max_size"
                                   class="form-input"
                                   min="512" max="10240"
                                   wire:dirty.class="border-yellow-400">
                            <div wire:dirty wire:target="images_max_size"
                                 class="absolute right-2 top-2 text-yellow-600 text-xs font-medium">
                                Unsaved
                            </div>
                        </div>
                        @error('images_max_size') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        <p class="text-sm text-gray-500 mt-1">Maximum file size for image uploads</p>
                    </div>
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" wire:model.live="images_organize_by_date" class="form-checkbox">
                        <span class="ml-2">Organize Images by Date</span>
                        <span wire:dirty wire:target="images_organize_by_date"
                              class="ml-2 text-yellow-600 text-xs font-medium">
                            (Unsaved)
                        </span>
                    </label>
                    <p class="text-sm text-gray-500 mt-1 ml-6">Store images in year/month folders (e.g., 2024/10/)</p>
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" wire:model.live="images_use_seo_filenames" class="form-checkbox">
                        <span class="ml-2">Use SEO-Friendly Filenames</span>
                        <span wire:dirty wire:target="images_use_seo_filenames"
                              class="ml-2 text-yellow-600 text-xs font-medium">
                            (Unsaved)
                        </span>
                    </label>
                    <p class="text-sm text-gray-500 mt-1 ml-6">Use readable filenames instead of random hashes</p>
                </div>
            </div>
            @endif

            <!-- Editor Settings Tab -->
            @if($activeTab === 'editor')
            <div class="space-y-4">
                <h3 class="text-lg font-semibold mb-4">Editor Settings</h3>

                <div>
                    <label class="form-label">Editor Type</label>
                    <div class="relative">
                        <select wire:model.live="editor_type"
                                class="form-select"
                                wire:dirty.class="border-yellow-400">
                            <option value="rich">Rich Text Editor (WYSIWYG)</option>
                            <option value="markdown">Markdown Editor</option>
                        </select>
                        <div wire:dirty wire:target="editor_type"
                             class="absolute right-2 top-2 text-yellow-600 text-xs font-medium pointer-events-none">
                            Unsaved
                        </div>
                    </div>
                    @error('editor_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    <p class="text-sm text-gray-500 mt-1">Choose the editor type for creating posts</p>
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" wire:model.live="editor_sanitize_html" class="form-checkbox">
                        <span class="ml-2">Sanitize HTML Content</span>
                        <span wire:dirty wire:target="editor_sanitize_html"
                              class="ml-2 text-yellow-600 text-xs font-medium">
                            (Unsaved)
                        </span>
                    </label>
                    <p class="text-sm text-gray-500 mt-1 ml-6">Remove potentially dangerous HTML tags for security</p>
                </div>
            </div>
            @endif

            <!-- Revisions Settings Tab -->
            @if($activeTab === 'revisions')
            <div class="space-y-4">
                <h3 class="text-lg font-semibold mb-4">Revision Settings</h3>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" wire:model.live="revisions_enabled" class="form-checkbox">
                        <span class="ml-2">Enable Post Revisions</span>
                        <span wire:dirty wire:target="revisions_enabled"
                              class="ml-2 text-yellow-600 text-xs font-medium">
                            (Unsaved)
                        </span>
                    </label>
                    <p class="text-sm text-gray-500 mt-1 ml-6">Automatically save post revisions when content changes</p>
                </div>

                <div>
                    <label class="form-label">Number of Revisions to Keep</label>
                    <div class="relative">
                        <input type="number" wire:model.blur="revisions_keep"
                               class="form-input"
                               min="1" max="50"
                               wire:dirty.class="border-yellow-400">
                        <div wire:dirty wire:target="revisions_keep"
                             class="absolute right-2 top-2 text-yellow-600 text-xs font-medium">
                            Unsaved
                        </div>
                    </div>
                    @error('revisions_keep') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    <p class="text-sm text-gray-500 mt-1">Maximum number of revisions to keep per post</p>
                </div>
            </div>
            @endif

            <!-- Activity Log Settings Tab -->
            @if($activeTab === 'activity_log')
            <div class="space-y-4">
                <h3 class="text-lg font-semibold mb-4">Activity Log Settings</h3>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" wire:model.live="activity_log_enabled" class="form-checkbox">
                        <span class="ml-2">Enable Activity Logging</span>
                        <span wire:dirty wire:target="activity_log_enabled"
                              class="ml-2 text-yellow-600 text-xs font-medium">
                            (Unsaved)
                        </span>
                    </label>
                    <p class="text-sm text-gray-500 mt-1 ml-6">Log admin actions for audit trail</p>
                </div>

                <div>
                    <label class="form-label">Days to Keep Activity Logs</label>
                    <div class="relative">
                        <input type="number" wire:model.blur="activity_log_keep_days"
                               class="form-input"
                               min="1" max="365"
                               wire:dirty.class="border-yellow-400">
                        <div wire:dirty wire:target="activity_log_keep_days"
                             class="absolute right-2 top-2 text-yellow-600 text-xs font-medium">
                            Unsaved
                        </div>
                    </div>
                    @error('activity_log_keep_days') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    <p class="text-sm text-gray-500 mt-1">Number of days to retain activity logs before cleanup</p>
                </div>
            </div>
            @endif

            <!-- Social Sharing Settings Tab -->
            @if($activeTab === 'social_share')
            <div class="space-y-4">
                <h3 class="text-lg font-semibold mb-4">Social Sharing Settings</h3>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" wire:model.live="social_share_enabled" class="form-checkbox">
                        <span class="ml-2">Enable Social Sharing</span>
                        <span wire:dirty wire:target="social_share_enabled"
                              class="ml-2 text-yellow-600 text-xs font-medium">
                            (Unsaved)
                        </span>
                    </label>
                    <p class="text-sm text-gray-500 mt-1 ml-6">Display social share buttons on blog posts</p>
                </div>

                @if($social_share_enabled)
                <div class="ml-6 pl-4 border-l-2 border-gray-200 space-y-3">
                    <p class="text-sm font-medium text-gray-700 mb-3">Select which platforms to enable:</p>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" wire:model.live="social_share_facebook" class="form-checkbox">
                            <span class="ml-2">Facebook</span>
                            <span wire:dirty wire:target="social_share_facebook"
                                  class="ml-2 text-yellow-600 text-xs font-medium">
                                (Unsaved)
                            </span>
                        </label>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" wire:model.live="social_share_twitter" class="form-checkbox">
                            <span class="ml-2">Twitter (X)</span>
                            <span wire:dirty wire:target="social_share_twitter"
                                  class="ml-2 text-yellow-600 text-xs font-medium">
                                (Unsaved)
                            </span>
                        </label>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" wire:model.live="social_share_linkedin" class="form-checkbox">
                            <span class="ml-2">LinkedIn</span>
                            <span wire:dirty wire:target="social_share_linkedin"
                                  class="ml-2 text-yellow-600 text-xs font-medium">
                                (Unsaved)
                            </span>
                        </label>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" wire:model.live="social_share_reddit" class="form-checkbox">
                            <span class="ml-2">Reddit</span>
                            <span wire:dirty wire:target="social_share_reddit"
                                  class="ml-2 text-yellow-600 text-xs font-medium">
                                (Unsaved)
                            </span>
                        </label>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" wire:model.live="social_share_whatsapp" class="form-checkbox">
                            <span class="ml-2">WhatsApp</span>
                            <span wire:dirty wire:target="social_share_whatsapp"
                                  class="ml-2 text-yellow-600 text-xs font-medium">
                                (Unsaved)
                            </span>
                        </label>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" wire:model.live="social_share_email" class="form-checkbox">
                            <span class="ml-2">Email</span>
                            <span wire:dirty wire:target="social_share_email"
                                  class="ml-2 text-yellow-600 text-xs font-medium">
                                (Unsaved)
                            </span>
                        </label>
                    </div>
                </div>
                @endif

                <div class="bg-blue-50 border border-blue-200 rounded p-4 mt-6">
                    <p class="text-sm text-blue-900">
                        <svg class="inline h-4 w-4 text-blue-600 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <strong>Tip:</strong> Social share buttons will appear at the end of each blog post, allowing readers to easily share your content on their preferred platforms.
                    </p>
                </div>
            </div>
            @endif

            <!-- Save Button at Bottom -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500">
                            <svg class="inline h-4 w-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            Settings are automatically saved when you change them. The "Save All" button can be used to force save all settings.
                        </p>
                    </div>
                    <button wire:click="saveSettings" class="btn btn-primary">
                        <span wire:loading.remove wire:target="saveSettings">Save All Settings</span>
                        <span wire:loading wire:target="saveSettings" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Saving...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
