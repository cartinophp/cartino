<?php

namespace Cartino\Console\Commands;

use Cartino\Support\Asset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class BuildAssetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cartino:build {--dev : Build for development} {--watch : Build and watch for changes}';

    /**
     * The console command description.
     */
    protected $description = 'Build Shopper frontend assets and publish them to public/vendor/cartino';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Building Shopper frontend assets...');

        // Change to package directory
        $packagePath = dirname(__DIR__, 3);

        if ($this->option('watch')) {
            $this->info('Starting development server with hot reload...');
            $result = Process::path($packagePath)->run('npm run dev');
        } elseif ($this->option('dev')) {
            $this->info('Building assets for development...');
            $result = Process::path($packagePath)->run('npm run build:dev');
        } else {
            $this->info('Building assets for production...');
            $result = Process::path($packagePath)->run('npm run build');
        }

        if ($result->failed()) {
            $this->error('Asset build failed:');
            $this->error($result->errorOutput());

            return Command::FAILURE;
        }

        $this->info('Assets built successfully!');

        // Only publish built assets if not in watch mode
        if (! $this->option('watch')) {
            $this->info('Publishing built assets...');

            $this->call('vendor:publish', [
                '--tag' => 'cartino-assets-built',
                '--force' => true,
            ]);

            // Test Asset helper
            if (Asset::isBuilt()) {
                $this->info('✅ Asset helper confirmed build is ready');
                $this->info('Main app URL: '.Asset::url('resources/js/app.js'));
            } else {
                $this->warn('⚠️  Asset helper cannot find manifest file');
            }

            $this->info('✅ Shopper assets built and published successfully!');
            $this->info('Assets are now available at: public/vendor/cartino/');
        }

        return Command::SUCCESS;
    }
}
