<?php

namespace Sajdoko\TallPress\Livewire\Admin\Settings;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Sajdoko\TallPress\Livewire\Concerns\WithToast;
use Sajdoko\TallPress\Models\ActivityLog;
use Sajdoko\TallPress\Services\SettingsService;

class Index extends Component
{
    use AuthorizesRequests, WithToast;

    // General settings
    public $per_page;

    public $route_prefix;

    public $admin_route_prefix;

    // TallPress settings
    public $tallpress_title;

    public $tallpress_description;

    public $tallpress_logo;

    // Search settings
    public $search_enabled;

    // Comments settings
    public $comments_enabled;

    public $comments_require_approval;

    // Images settings
    public $images_max_size;

    public $images_organize_by_date;

    public $images_use_seo_filenames;

    // Editor settings
    public $editor_type;

    public $editor_sanitize_html;

    // Revisions settings
    public $revisions_enabled;

    public $revisions_keep;

    // Activity Log settings
    public $activity_log_enabled;

    public $activity_log_keep_days;

    // Social Share settings
    public $social_share_enabled;

    public $social_share_facebook;

    public $social_share_twitter;

    public $social_share_linkedin;

    public $social_share_reddit;

    public $social_share_whatsapp;

    public $social_share_email;

    public $activeTab = 'general';

    // Track saving state for user feedback
    public $saving = false;

    public $lastSaved = null;

    protected $settingsService;

    protected function rules()
    {
        return [
            'per_page' => 'required|integer|min:1|max:100',
            'route_prefix' => 'required|string|max:255',
            'admin_route_prefix' => 'required|string|max:255',
            'tallpress_title' => 'nullable|string|max:255',
            'tallpress_description' => 'nullable|string|max:500',
            'tallpress_logo' => 'nullable|string|max:255',
            'search_enabled' => 'boolean',
            'comments_enabled' => 'boolean',
            'comments_require_approval' => 'boolean',
            'images_max_size' => 'required|integer|min:512|max:10240',
            'images_organize_by_date' => 'boolean',
            'images_use_seo_filenames' => 'boolean',
            'editor_type' => 'required|in:rich,markdown',
            'editor_sanitize_html' => 'boolean',
            'revisions_enabled' => 'boolean',
            'revisions_keep' => 'required|integer|min:1|max:50',
            'activity_log_enabled' => 'boolean',
            'activity_log_keep_days' => 'required|integer|min:1|max:365',
            'social_share_enabled' => 'boolean',
            'social_share_facebook' => 'boolean',
            'social_share_twitter' => 'boolean',
            'social_share_linkedin' => 'boolean',
            'social_share_reddit' => 'boolean',
            'social_share_whatsapp' => 'boolean',
            'social_share_email' => 'boolean',
        ];
    }

    public function boot(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    public function mount(SettingsService $settingsService)
    {
        $this->authorize('manageSettings', \Sajdoko\TallPress\Models\Post::class);

        // Load current settings from service (with fallback to config)
        $this->per_page = $settingsService->get('per_page', 15);
        $this->route_prefix = $settingsService->get('route_prefix', 'blog');
        $this->admin_route_prefix = $settingsService->get('admin_route_prefix', 'admin/blog');

        $this->tallpress_title = $settingsService->get('tallpress_title', '');
        $this->tallpress_description = $settingsService->get('tallpress_description', '');
        $this->tallpress_logo = $settingsService->get('tallpress_logo', '');

        $this->search_enabled = $settingsService->get('search_enabled', true);

        $this->comments_enabled = $settingsService->get('comments_enabled', true);
        $this->comments_require_approval = $settingsService->get('comments_require_approval', true);

        $this->images_max_size = $settingsService->get('images_max_size', 2048);
        $this->images_organize_by_date = $settingsService->get('images_organize_by_date', true);
        $this->images_use_seo_filenames = $settingsService->get('images_use_seo_filenames', true);

        $this->editor_type = $settingsService->get('editor_type', 'rich');
        $this->editor_sanitize_html = $settingsService->get('editor_sanitize_html', true);

        $this->revisions_enabled = $settingsService->get('revisions_enabled', true);
        $this->revisions_keep = $settingsService->get('revisions_keep', 10);

        $this->activity_log_enabled = $settingsService->get('activity_log_enabled', true);
        $this->activity_log_keep_days = $settingsService->get('activity_log_keep_days', 90);

        $this->social_share_enabled = $settingsService->get('social_share_enabled', false);
        $this->social_share_facebook = $settingsService->get('social_share_facebook', true);
        $this->social_share_twitter = $settingsService->get('social_share_twitter', true);
        $this->social_share_linkedin = $settingsService->get('social_share_linkedin', true);
        $this->social_share_reddit = $settingsService->get('social_share_reddit', false);
        $this->social_share_whatsapp = $settingsService->get('social_share_whatsapp', false);
        $this->social_share_email = $settingsService->get('social_share_email', true);
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    /**
     * Real-time saving hook - called whenever a property is updated
     */
    public function updated($propertyName, $value)
    {
        // Skip if this is the activeTab or saving state properties
        if (in_array($propertyName, ['activeTab', 'saving', 'lastSaved'])) {
            return;
        }

        $this->saving = true;

        try {
            // Validate only the changed property
            $this->validateOnly($propertyName);

            // Determine the group and type for this setting
            $settingMetadata = $this->getSettingMetadata($propertyName);

            if ($settingMetadata) {
                // Save the individual setting
                $this->settingsService->set(
                    $propertyName,
                    $value,
                    $settingMetadata['type'],
                    $settingMetadata['group']
                );

                // Clear route cache if route prefix changed
                if (in_array($propertyName, ['route_prefix', 'admin_route_prefix'])) {
                    \Illuminate\Support\Facades\Artisan::call('route:clear');
                    $this->toastInfo('Route cache cleared. Changes will take effect immediately.');
                }

                // Update last saved timestamp
                $this->lastSaved = now()->format('g:i A');

                // Log activity if enabled
                if (tallpress_setting('activity_log_enabled', true)) {
                    ActivityLog::log('updated setting', null, auth()->user(), [
                        'setting' => $propertyName,
                        'value' => $value,
                        'tab' => $this->activeTab,
                    ]);
                }
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors will be shown automatically
            throw $e;
        } finally {
            $this->saving = false;
        }
    }

    /**
     * Get metadata for a setting (group and type)
     */
    protected function getSettingMetadata($propertyName)
    {
        $metadata = [
            'per_page' => ['type' => 'integer', 'group' => 'general'],
            'route_prefix' => ['type' => 'string', 'group' => 'general'],
            'admin_route_prefix' => ['type' => 'string', 'group' => 'general'],

            'tallpress_title' => ['type' => 'string', 'group' => 'blog'],
            'tallpress_description' => ['type' => 'string', 'group' => 'blog'],
            'tallpress_logo' => ['type' => 'string', 'group' => 'blog'],

            'search_enabled' => ['type' => 'boolean', 'group' => 'search'],

            'comments_enabled' => ['type' => 'boolean', 'group' => 'comments'],
            'comments_require_approval' => ['type' => 'boolean', 'group' => 'comments'],

            'images_max_size' => ['type' => 'integer', 'group' => 'images'],
            'images_organize_by_date' => ['type' => 'boolean', 'group' => 'images'],
            'images_use_seo_filenames' => ['type' => 'boolean', 'group' => 'images'],

            'editor_type' => ['type' => 'string', 'group' => 'editor'],
            'editor_sanitize_html' => ['type' => 'boolean', 'group' => 'editor'],

            'revisions_enabled' => ['type' => 'boolean', 'group' => 'revisions'],
            'revisions_keep' => ['type' => 'integer', 'group' => 'revisions'],

            'activity_log_enabled' => ['type' => 'boolean', 'group' => 'activity_log'],
            'activity_log_keep_days' => ['type' => 'integer', 'group' => 'activity_log'],

            'social_share_enabled' => ['type' => 'boolean', 'group' => 'social_share'],
            'social_share_facebook' => ['type' => 'boolean', 'group' => 'social_share'],
            'social_share_twitter' => ['type' => 'boolean', 'group' => 'social_share'],
            'social_share_linkedin' => ['type' => 'boolean', 'group' => 'social_share'],
            'social_share_reddit' => ['type' => 'boolean', 'group' => 'social_share'],
            'social_share_whatsapp' => ['type' => 'boolean', 'group' => 'social_share'],
            'social_share_email' => ['type' => 'boolean', 'group' => 'social_share'],
        ];

        return $metadata[$propertyName] ?? null;
    }

    public function saveSettings()
    {
        $this->authorize('manageSettings', \Sajdoko\TallPress\Models\Post::class);

        $this->validate();

        // Save all settings
        $settings = [
            'per_page' => ['value' => $this->per_page, 'type' => 'integer', 'group' => 'general'],
            'route_prefix' => ['value' => $this->route_prefix, 'type' => 'string', 'group' => 'general'],
            'admin_route_prefix' => ['value' => $this->admin_route_prefix, 'type' => 'string', 'group' => 'general'],

            'tallpress_title' => ['value' => $this->tallpress_title, 'type' => 'string', 'group' => 'blog'],
            'tallpress_description' => ['value' => $this->tallpress_description, 'type' => 'string', 'group' => 'blog'],
            'tallpress_logo' => ['value' => $this->tallpress_logo, 'type' => 'string', 'group' => 'blog'],

            'search_enabled' => ['value' => $this->search_enabled, 'type' => 'boolean', 'group' => 'search'],

            'comments_enabled' => ['value' => $this->comments_enabled, 'type' => 'boolean', 'group' => 'comments'],
            'comments_require_approval' => ['value' => $this->comments_require_approval, 'type' => 'boolean', 'group' => 'comments'],

            'images_max_size' => ['value' => $this->images_max_size, 'type' => 'integer', 'group' => 'images'],
            'images_organize_by_date' => ['value' => $this->images_organize_by_date, 'type' => 'boolean', 'group' => 'images'],
            'images_use_seo_filenames' => ['value' => $this->images_use_seo_filenames, 'type' => 'boolean', 'group' => 'images'],

            'editor_type' => ['value' => $this->editor_type, 'type' => 'string', 'group' => 'editor'],
            'editor_sanitize_html' => ['value' => $this->editor_sanitize_html, 'type' => 'boolean', 'group' => 'editor'],

            'revisions_enabled' => ['value' => $this->revisions_enabled, 'type' => 'boolean', 'group' => 'revisions'],
            'revisions_keep' => ['value' => $this->revisions_keep, 'type' => 'integer', 'group' => 'revisions'],

            'activity_log_enabled' => ['value' => $this->activity_log_enabled, 'type' => 'boolean', 'group' => 'activity_log'],
            'activity_log_keep_days' => ['value' => $this->activity_log_keep_days, 'type' => 'integer', 'group' => 'activity_log'],

            'social_share_enabled' => ['value' => $this->social_share_enabled, 'type' => 'boolean', 'group' => 'social_share'],
            'social_share_facebook' => ['value' => $this->social_share_facebook, 'type' => 'boolean', 'group' => 'social_share'],
            'social_share_twitter' => ['value' => $this->social_share_twitter, 'type' => 'boolean', 'group' => 'social_share'],
            'social_share_linkedin' => ['value' => $this->social_share_linkedin, 'type' => 'boolean', 'group' => 'social_share'],
            'social_share_reddit' => ['value' => $this->social_share_reddit, 'type' => 'boolean', 'group' => 'social_share'],
            'social_share_whatsapp' => ['value' => $this->social_share_whatsapp, 'type' => 'boolean', 'group' => 'social_share'],
            'social_share_email' => ['value' => $this->social_share_email, 'type' => 'boolean', 'group' => 'social_share'],
        ];

        $this->settingsService->setMany($settings);

        // Clear route cache to ensure route prefix changes take effect
        \Illuminate\Support\Facades\Artisan::call('route:clear');

        // Log activity
        if (tallpress_setting('activity_log_enabled', true)) {
            ActivityLog::log('updated settings', null, auth()->user(), [
                'tab' => $this->activeTab,
            ]);
        }

        $this->toastSuccess('Settings saved successfully. Route cache has been cleared.');
    }

    public function resetToDefaults()
    {
        $this->authorize('manageSettings', \Sajdoko\TallPress\Models\Post::class);

        // Initialize/reset defaults from config to database
        $this->settingsService->initializeDefaults();

        // Reload settings
        $this->mount($this->settingsService);

        session()->flash('success', 'Settings reset to defaults.');
        $this->redirectRoute('tallpress.admin.settings.index');
    }

    public function render()
    {
        return view('tallpress::admin.livewire.settings.index')
            ->layout('tallpress::admin.layout');
    }
}
