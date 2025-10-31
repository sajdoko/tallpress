<?php

namespace Sajdoko\TallPress;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Sajdoko\TallPress\Commands\TallPressCleanCommand;
use Sajdoko\TallPress\Commands\TallPressInitSettingsCommand;
use Sajdoko\TallPress\Commands\TallPressInstallCommand;
use Sajdoko\TallPress\Commands\TallPressReorganizeImagesCommand;
use Sajdoko\TallPress\Http\Middleware\EnsureTallPressRole;
use Sajdoko\TallPress\Models\Category;
use Sajdoko\TallPress\Models\Media;
use Sajdoko\TallPress\Models\Post;
use Sajdoko\TallPress\Models\Tag;
use Sajdoko\TallPress\Observers\CategoryObserver;
use Sajdoko\TallPress\Observers\PostObserver;
use Sajdoko\TallPress\Observers\TagObserver;
use Sajdoko\TallPress\Policies\MediaPolicy;
use Sajdoko\TallPress\Policies\PostPolicy;
use Sajdoko\TallPress\Services\SettingsService;

class TallPressServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge package config
        $this->mergeConfigFrom(__DIR__.'/../config/tallpress.php', 'tallpress');

        // Register SettingsService as singleton
        $this->app->singleton(SettingsService::class, function ($app) {
            return new SettingsService;
        });

        // Load helper functions
        if (file_exists(__DIR__.'/helpers.php')) {
            require_once __DIR__.'/helpers.php';
        }
    }

    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__.'/../config/tallpress.php' => config_path('tallpress.php'),
        ], 'tallpress-config');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/tallpress'),
        ], 'tallpress-views');

        // Publish translations
        $this->publishes([
            __DIR__.'/../resources/lang' => lang_path('vendor/tallpress'),
        ], 'tallpress-lang');

        // Publish assets
        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/tallpress'),
        ], 'tallpress-assets');

        // Load views from package
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tallpress');

        // Load translations from package
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'tallpress');

        // Load view components
        $this->loadViewComponentsAs('blog', [
            // Add your view components here if you have any
        ]);

        // Load migrations from package
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                TallPressInstallCommand::class,
                TallPressCleanCommand::class,
                TallPressReorganizeImagesCommand::class,
                TallPressInitSettingsCommand::class,
            ]);
        }

        // Register middleware first (no dependencies)
        $this->registerMiddleware();

        // Skip Livewire-dependent registrations during package discovery
        // Check if we're specifically running the package:discover command
        $isPackageDiscovery = $this->app->runningInConsole() &&
                               isset($_SERVER['argv']) &&
                               in_array('package:discover', $_SERVER['argv']);

        if (! $isPackageDiscovery) {
            // Defer these registrations until after all providers are booted
            // This ensures Livewire is available when route files with Livewire component imports are loaded
            $this->app->booted(function () {
                $this->registerLivewireComponents();
                $this->registerRoutes();
                $this->servePackageAssets();
            });
        }

        // Register gates
        $this->registerGates();

        // Register policies
        $this->registerPolicies();

        // Register observers
        Post::observe(PostObserver::class);
        Category::observe(CategoryObserver::class);
        Tag::observe(TagObserver::class);

        // Register User relationship to posts
        $this->registerUserRelationships();
    }

    protected function registerLivewireComponents(): void
    {
        // Only register if Livewire is available
        if (! class_exists(\Livewire\Livewire::class)) {
            return;
        }

        // Frontend Components
        Livewire::component('tallpress-search', \Sajdoko\TallPress\Livewire\Front\Search::class);
        Livewire::component('tallpress-search-widget', \Sajdoko\TallPress\Livewire\Front\SearchWidget::class);
        Livewire::component('tallpress-comments', \Sajdoko\TallPress\Livewire\Front\Comments::class);

        // Dashboard
        Livewire::component('tallpress.admin.dashboard', \Sajdoko\TallPress\Livewire\Admin\Dashboard::class);

        // Posts
        Livewire::component('tallpress.admin.posts.index', \Sajdoko\TallPress\Livewire\Admin\Posts\Index::class);
        Livewire::component('tallpress.admin.posts.create-edit', \Sajdoko\TallPress\Livewire\Admin\Posts\CreateEdit::class);
        Livewire::component('tallpress.admin.posts.revisions', \Sajdoko\TallPress\Livewire\Admin\Posts\Revisions::class);

        // Categories
        Livewire::component('tallpress.admin.categories.index', \Sajdoko\TallPress\Livewire\Admin\Categories\Index::class);

        // Tags
        Livewire::component('tallpress.admin.tags.index', \Sajdoko\TallPress\Livewire\Admin\Tags\Index::class);

        // Comments
        Livewire::component('tallpress.admin.comments.index', \Sajdoko\TallPress\Livewire\Admin\Comments\Index::class);

        // Media
        Livewire::component('tallpress.admin.media.manager', \Sajdoko\TallPress\Livewire\Admin\Media\Manager::class);
        Livewire::component('tallpress.admin.media.picker', \Sajdoko\TallPress\Livewire\Admin\Media\Picker::class);

        // Users
        Livewire::component('tallpress.admin.users.index', \Sajdoko\TallPress\Livewire\Admin\Users\Index::class);

        // Settings
        Livewire::component('tallpress.admin.settings.index', \Sajdoko\TallPress\Livewire\Admin\Settings\Index::class);
    }

    protected function registerRoutes(): void
    {
        // Get route prefixes from settings service (with fallback to config)
        $settingsService = $this->app->make(SettingsService::class);
        $webPrefix = $settingsService->get('route_prefix', config('tallpress.route_prefix', 'blog'));
        $adminPrefix = $settingsService->get('admin_route_prefix', config('tallpress.admin_route_prefix', 'admin/blog'));

        // Web routes
        \Illuminate\Support\Facades\Route::middleware('web')
            ->prefix($webPrefix)
            ->group(__DIR__.'/../routes/web.php');

        // API routes
        \Illuminate\Support\Facades\Route::prefix('api/'.$webPrefix)
            ->group(__DIR__.'/../routes/api.php');

        // Admin routes
        \Illuminate\Support\Facades\Route::middleware('web')
            ->prefix($adminPrefix)
            ->group(__DIR__.'/../routes/admin.php');
    }

    protected function registerMiddleware(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('tallpress.role', EnsureTallPressRole::class);
    }

    protected function servePackageAssets(): void
    {
        // Serve assets from package directory (development mode)
        \Illuminate\Support\Facades\Route::middleware('web')
            ->get('vendor/tallpress/{path}', function ($path) {
                $file = __DIR__.'/../public/'.$path;

                if (! file_exists($file)) {
                    abort(404);
                }

                $mimeTypes = [
                    'css' => 'text/css',
                    'js' => 'application/javascript',
                    'png' => 'image/png',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'gif' => 'image/gif',
                    'svg' => 'image/svg+xml',
                ];

                $extension = pathinfo($file, PATHINFO_EXTENSION);
                $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

                return response()->file($file, [
                    'Content-Type' => $mimeType,
                    'Cache-Control' => 'public, max-age=31536000',
                ]);
            })->where('path', '.*')->name('tallpress.assets');
    }

    protected function registerGates(): void
    {
        Gate::define('access-tallpress-admin', function ($user) {
            $roleField = config('tallpress.roles.role_field', 'role');
            $userRole = $user->{$roleField} ?? null;

            return in_array($userRole, ['admin', 'editor', 'author']);
        });
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(Media::class, MediaPolicy::class);
    }

    protected function registerUserRelationships(): void
    {
        // Dynamically add posts relationship to User model
        $userModel = config('tallpress.author_model', 'App\\Models\\User');

        if (class_exists($userModel)) {
            $userModel::resolveRelationUsing('posts', function ($userModel) {
                return $userModel->hasMany(Post::class, 'author_id');
            });
        }
    }
}
