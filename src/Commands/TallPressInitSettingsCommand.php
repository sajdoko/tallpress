<?php

namespace Sajdoko\TallPress\Commands;

use Illuminate\Console\Command;
use Sajdoko\TallPress\Services\SettingsService;

class TallPressInitSettingsCommand extends Command
{
    protected $signature = 'tallpress:init-settings
                            {--force : Force overwrite existing settings}';

    protected $description = 'Initialize blog settings from config file';

    public function handle(SettingsService $settingsService): int
    {
        $this->info('Initializing blog settings...');

        if ($this->option('force')) {
            $this->warn('Force mode: Existing settings will be overwritten with config values.');

            if (! $this->confirm('Are you sure you want to continue?')) {
                $this->info('Operation cancelled.');

                return self::SUCCESS;
            }
        }

        $settingsService->initializeDefaults();

        $this->info('✓ Blog settings initialized successfully!');
        $this->line('');
        $this->line('You can now manage settings from the admin panel:');
        $this->line('  '.url(config('tallpress.admin_route_prefix', 'admin/blog').'/settings'));

        return self::SUCCESS;
    }
}
