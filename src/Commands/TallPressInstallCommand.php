<?php

namespace Sajdoko\TallPress\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class TallPressInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tallpress:install {--seed : Seed the database with sample data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the blog package';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Installing Blog Package...');

        // Publish config first so we can use it
        $this->info('Publishing configuration...');
        Artisan::call('vendor:publish', [
            '--provider' => 'Sajdoko\\TallPress\\TallPressServiceProvider',
            '--tag' => 'tallpress-config',
            '--force' => true,
        ]);

        // Detect and configure roles system BEFORE migrations
        $this->configureRolesSystem();

        // Publish views
        $this->info('Publishing views...');
        Artisan::call('vendor:publish', [
            '--provider' => 'Sajdoko\\TallPress\\TallPressServiceProvider',
            '--tag' => 'tallpress-views',
            '--force' => true,
        ]);

        // Publish translations
        $this->info('Publishing translations...');
        Artisan::call('vendor:publish', [
            '--provider' => 'Sajdoko\\TallPress\\TallPressServiceProvider',
            '--tag' => 'tallpress-lang',
            '--force' => true,
        ]);

        // Publish assets
        $this->info('Publishing assets...');
        Artisan::call('vendor:publish', [
            '--provider' => 'Sajdoko\\TallPress\\TallPressServiceProvider',
            '--tag' => 'tallpress-assets',
            '--force' => true,
        ]);

        // Run migrations AFTER config is set
        $this->info('Running migrations...');
        Artisan::call('migrate');
        $this->info(Artisan::output());

        // Initialize settings
        $this->info('Initializing settings...');
        Artisan::call('tallpress:init-settings');

        // Seed if requested
        if ($this->option('seed')) {
            $this->info('Seeding database...');
            $seeder = new \Sajdoko\TallPress\Database\Seeders\TallPressSeeder;
            $seeder->setCommand($this);
            $seeder->run();
        }

        $this->info('Blog package installed successfully!');
        $this->line('');
        $this->line('Next steps:');
        $this->line('1. Configure the package in config/tallpress.php');
        $this->line('2. Customize views in resources/views/vendor/tallpress');
        $this->line('3. Visit /blog to see your blog');
        $this->line('');
        $this->line('For more information, see: https://github.com/sajdoko/tallpress/wiki');

        return self::SUCCESS;
    }

    /**
     * Detect and configure the roles system.
     */
    protected function configureRolesSystem(): void
    {
        $this->info('Checking roles system...');

        // Check if common ACL packages are installed
        $hasSpatie = class_exists(\Spatie\Permission\PermissionServiceProvider::class);
        $hasBouncer = class_exists(\Silber\Bouncer\BouncerServiceProvider::class);
        $hasLaratrust = class_exists(\Laratrust\LaratrustServiceProvider::class);

        // Check if users table has role-related columns/tables from external ACL packages
        $hasRolesTable = Schema::hasTable('roles');
        $hasModelHasRolesTable = Schema::hasTable('model_has_roles');
        $hasPermissionsTable = Schema::hasTable('permissions');

        if ($hasSpatie || $hasBouncer || $hasLaratrust || $hasRolesTable || $hasModelHasRolesTable || $hasPermissionsTable) {
            // External ACL system detected
            $this->warn('⚠️  External roles/permissions system detected!');

            if ($hasSpatie) {
                $this->line('   Detected: Spatie Laravel Permission');
            } elseif ($hasBouncer) {
                $this->line('   Detected: Bouncer');
            } elseif ($hasLaratrust) {
                $this->line('   Detected: Laratrust');
            } else {
                $this->line('   Detected: Custom roles system');
            }

            $this->line('');
            $this->line('The package will NOT add a role column to the users table.');
            $this->line('You need to manually configure authorization in your AppServiceProvider:');
            $this->line('');
            $this->line('Gate::define(\'access-tallpress-admin\', function ($user) {');
            $this->line('    return $user->hasRole([\'admin\', \'editor\', \'author\']);');
            $this->line('});');
            $this->line('');

            // Update config to disable role field
            $this->updateConfigFile('add_role_field', false);

        } else {
            // No external ACL detected - use package's built-in role column
            $hasRoleColumn = Schema::hasTable('users') && Schema::hasColumn('users', 'role');

            if ($hasRoleColumn) {
                $this->info('✓ Role column already exists in users table - package will manage roles');
            } else {
                $this->info('✓ No roles system detected - package will add a role column to users table');
            }

            $this->updateConfigFile('add_role_field', true);
        }
    }

    /**
     * Update a configuration value in the published config file.
     */
    protected function updateConfigFile(string $key, $value): void
    {
        $configPath = config_path('tallpress.php');

        if (! file_exists($configPath)) {
            return;
        }

        $content = file_get_contents($configPath);

        // Update the specific config value
        if ($key === 'add_role_field') {
            $pattern = "/'add_role_field'\s*=>\s*(true|false)/";
            $replacement = "'add_role_field' => ".($value ? 'true' : 'false');
            $content = preg_replace($pattern, $replacement, $content);
        }

        file_put_contents($configPath, $content);

        // Reload the config in memory
        config(['tallpress.roles.add_role_field' => $value]);
    }
}
