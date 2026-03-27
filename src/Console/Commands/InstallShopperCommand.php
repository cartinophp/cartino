<?php

namespace Cartino\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallShopperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cartino:install 
                          {--oauth : Install OAuth authentication system}
                          {--force : Overwrite existing files}
                          {--seed : Run database seeders and create sample data}
                          {--admin : Create admin user after installation}
                          {--skip-migrations : Skip running migrations}
                          {--skip-assets : Skip publishing assets}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Cartino package';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🛍️  Installing Cartino...');
        $this->newLine();

        // Check Laravel version
        if (! $this->checkLaravelVersion()) {
            return 1;
        }

        // Publish configuration files
        $this->publishConfiguration();

        // Publish permissions configuration
        $this->publishPermissionsConfiguration();

        // Publish OAuth system if requested
        if ($this->option('oauth')) {
            $this->publishOAuthSystem();
        }

        // Run migrations unless skipped
        if (! $this->option('skip-migrations')) {
            $this->runMigrations();
        }

        // Publish assets unless skipped
        if (! $this->option('skip-assets')) {
            $this->publishAssets();
        }

        // Run seeders if requested
        if ($this->option('seed')) {
            $this->runSeeders();
        }

        // Create admin user if requested
        if ($this->option('admin')) {
            $this->createAdminUser();
        }

        // Display completion message
        $this->displayCompletionMessage();

        return 0;
    }

    /**
     * Check Laravel version compatibility.
     */
    protected function checkLaravelVersion(): bool
    {
        $laravelVersion = app()->version();

        if (version_compare($laravelVersion, '11.0', '<')) {
            $this->error('❌ Cartino requires Laravel 11.0 or higher.');
            $this->error("   Current version: {$laravelVersion}");

            return false;
        }

        $this->info("✅ Laravel version {$laravelVersion} is compatible.");

        return true;
    }

    /**
     * Publish configuration files.
     */
    protected function publishConfiguration(): void
    {
        $this->info('📝 Publishing configuration files...');

        $params = ['--provider' => 'Cartino\CartinoServiceProvider'];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        Artisan::call('vendor:publish', array_merge($params, ['--tag' => 'cartino-config']));

        $this->info('✅ Configuration published successfully.');
    }

    /**
     * Publish permissions configuration.
     */
    protected function publishPermissionsConfiguration(): void
    {
        $this->info('🔑 Publishing permissions configuration...');

        $params = ['--provider' => 'Cartino\CartinoServiceProvider'];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        Artisan::call('vendor:publish', array_merge($params, ['--tag' => 'cartino-permission-config']));

        $this->info('✅ Permissions configuration published successfully.');
    }

    /**
     * Publish OAuth authentication system.
     */
    protected function publishOAuthSystem(): void
    {
        $this->info('🔐 Publishing OAuth authentication system...');

        $params = ['--provider' => 'Cartino\CartinoServiceProvider'];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        // Publish OAuth configuration
        Artisan::call('vendor:publish', array_merge($params, ['--tag' => 'cartino-oauth-config']));

        // Publish Vue components
        Artisan::call('vendor:publish', array_merge($params, ['--tag' => 'cartino-components']));

        // Publish translations
        Artisan::call('vendor:publish', array_merge($params, ['--tag' => 'cartino-lang']));

        $this->info('✅ OAuth system published successfully.');

        // Display OAuth setup instructions
        $this->displayOAuthInstructions();
    }

    /**
     * Run database migrations.
     */
    protected function runMigrations(): void
    {
        $this->info('🗄️  Running database migrations...');

        if ($this->confirm('Do you want to run migrations now?', true)) {
            Artisan::call('migrate');
            $this->info('✅ Migrations completed successfully.');
        } else {
            $this->warn('⚠️  Skipped migrations. Run "php artisan migrate" manually when ready.');
        }
    }

    /**
     * Publish assets.
     */
    protected function publishAssets(): void
    {
        $this->info('🎨 Publishing assets...');

        $params = ['--provider' => 'Cartino\CartinoServiceProvider'];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        // Publish source assets
        Artisan::call('vendor:publish', array_merge($params, ['--tag' => 'cartino-assets']));
        Artisan::call('vendor:publish', array_merge($params, ['--tag' => 'cartino-views']));

        // Build and publish compiled assets
        $this->info('🔨 Building frontend assets...');
        $result = Artisan::call('cartino:build');

        if ($result === 0) {
            $this->info('✅ Assets built and published successfully.');
        } else {
            $this->warn('⚠️  Asset build completed with warnings. Assets published anyway.');
        }
    }

    /**
     * Run database seeders.
     */
    protected function runSeeders(): void
    {
        $this->info('🌱 Running database seeders...');

        try {
            // First, ensure migrations are run
            if (! $this->option('skip-migrations')) {
                $this->info('   Ensuring migrations are up to date...');
                Artisan::call('migrate', ['--force' => true]);
            }

            // Run the Shopper seeder
            $this->info('   Seeding roles, permissions, and sample data...');

            $exitCode = Artisan::call('db:seed', [
                '--class' => 'Cartino\\Database\\Seeders\\CartinoSeeder',
                '--force' => true,
            ]);

            if ($exitCode === 0) {
                $this->info('✅ Seeders completed successfully.');
                $this->line('   ✓ Roles and permissions created');
                $this->line('   ✓ Sample admin user created (admin@admin.com / password)');
                $this->line('   ✓ Basic store data seeded');
            } else {
                throw new \Exception('Seeder returned non-zero exit code: '.$exitCode);
            }
        } catch (\Exception $e) {
            $this->warn('⚠️  Seeder failed: '.$e->getMessage());
            $this->line(
                '   You can run seeders manually: php artisan db:seed --class=Cartino\\Database\\Seeders\\CartinoSeeder',
            );
        }
    }

    /**
     * Create admin user.
     */
    protected function createAdminUser(): void
    {
        $this->info('👤 Creating admin user...');

        try {
            Artisan::call('cartino:admin');
            $this->info('✅ Admin user creation process completed.');
        } catch (\Exception $e) {
            $this->warn('⚠️  Admin user creation failed: '.$e->getMessage());
            $this->line('   You can create an admin user manually: php artisan cartino:admin');
        }
    }

    /**
     * Display OAuth setup instructions.
     */
    protected function displayOAuthInstructions(): void
    {
        $this->newLine();
        $this->info('🔐 OAuth Setup Instructions:');
        $this->line('');
        $this->line('1. Add OAuth provider credentials to your .env file:');
        $this->line('   GOOGLE_CLIENT_ID=your_google_client_id');
        $this->line('   GOOGLE_CLIENT_SECRET=your_google_client_secret');
        $this->line('   FACEBOOK_CLIENT_ID=your_facebook_client_id');
        $this->line('   FACEBOOK_CLIENT_SECRET=your_facebook_client_secret');
        $this->line('   (... and so on for other providers)');
        $this->line('');
        $this->line('2. Configure OAuth callbacks in your provider apps:');
        $this->line('   - Google: https://yourapp.com/auth/social/google/callback');
        $this->line('   - Facebook: https://yourapp.com/auth/social/facebook/callback');
        $this->line('   - GitHub: https://yourapp.com/auth/social/github/callback');
        $this->line('   (... and so on for other providers)');
        $this->line('');
        $this->line('3. Use the SocialAuthComponent in your Vue.js templates');
        $this->line('');
        $this->info('📖 See OAUTH_SETUP.md for detailed instructions.');
    }

    /**
     * Display installation completion message.
     */
    protected function displayCompletionMessage(): void
    {
        $this->newLine();
        $this->info('🎉 Cartino installation completed successfully!');
        $this->line('');

        // Show what was installed
        $this->line('✅ Configuration published');
        $this->line('✅ Permissions system configured');

        if ($this->option('oauth')) {
            $this->line('✅ OAuth authentication system installed');
        }

        if (! $this->option('skip-assets')) {
            $this->line('✅ Assets published');
        }

        if (! $this->option('skip-migrations')) {
            $this->line('✅ Database migrations completed');
        }

        if ($this->option('seed')) {
            $this->line('✅ Sample data seeded');
            $this->line('✅ Roles and permissions created');
        }

        if ($this->option('admin')) {
            $this->line('✅ Admin user created');
        }

        $this->line('');
        $this->info('🚀 Next steps:');

        if (! $this->option('skip-migrations') && ! $this->option('seed')) {
            $this->line('1. Run migrations: php artisan migrate');
            $this->line('2. Seed data: php artisan db:seed --class=Cartino\\Database\\Seeders\\CartinoSeeder');
        }

        if (! $this->option('admin') && $this->option('seed')) {
            $this->line('• Login with default admin: admin@admin.com / password');
        } elseif (! $this->option('admin')) {
            $this->line('• Create admin user: php artisan cartino:admin');
        }

        if ($this->option('oauth')) {
            $this->line('• Configure OAuth provider credentials in .env');
        }

        $this->line('• Access Control Panel at: /cp');
        $this->line('');
        $this->info('📖 Documentation: https://github.com/vitalijalbu/laravel-cartino');
        $this->newLine();
    }
}
