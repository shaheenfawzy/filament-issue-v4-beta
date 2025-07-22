<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupCommand extends Command
{
    protected $signature = 'setup {--force : Force run the setup even if it has been run before}';
    protected $description = 'Run initial application setup';

    public function handle()
    {
        $lockFile = '.setup_completed';

        if (Storage::disk('local')->exists($lockFile) && ! $this->option('force')) {
            $this->components->warn('Application has already been set up.');
            $this->components->info('If you need to run setup again, use the --force flag:');
            $this->line('php artisan app:setup --force');

            return 1;
        }

        try {
            if (empty(config('app.key'))) {
                $this->components->info('🔑 Generating new application key...');
                Artisan::call('key:generate');
            } else {
                $this->line('✅ Application key already exists, skipping.');
            }

            $this->components->info('🏃 Running database migrations and seeding...');

            if ($this->option('force')) {
                $this->components->warn('Using --force: Dropping all tables before migrating.');
                Artisan::call('migrate:fresh', ['--seed' => true]);
            } else {
                Artisan::call('migrate', ['--seed' => true, '--force' => true]);
            }

            $this->components->info('Database migrated and seeded successfully.');

            Storage::disk('local')->put($lockFile, 'Setup completed on: '.now());

            $this->components->info('Created setup lock file.');

        } catch (\Exception $e) {
            $this->components->error('An error occurred during setup:');
            $this->components->error($e->getMessage());
            return 1;
        }

        $this->components->info('✅ Application setup is complete!');

        return 0;
    }
}
