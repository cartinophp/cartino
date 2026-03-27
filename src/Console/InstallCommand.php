<?php

namespace Cartino\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;

class InstallCommand extends Command
{
    protected $signature = 'cartino:install {--force : Overwrite existing files}';

    protected $description = 'Install Cartino package';

    public function handle(): int
    {
        $this->info('Installing Cartino...');

        // Publish config
        $this->info('Publishing configuration...');
        Artisan::call('vendor:publish', [
            '--tag' => 'cartino-config',
            '--force' => $this->option('force'),
        ]);

        // Publish migrations
        $this->info('Publishing migrations...');
        Artisan::call('vendor:publish', [
            '--tag' => 'cartino-core-migrations',
            '--force' => $this->option('force'),
        ]);

        // Run migrations
        if ($this->confirm('Would you like to run the migrations now?')) {
            $this->info('Running migrations...');
            Artisan::call('migrate');
            $this->info('Migrations completed.');
        }

        // Run seeders
        if ($this->confirm('Would you like to seed the database with sample data?')) {
            $this->info('Seeding database...');
            Artisan::call('db:seed', [
                '--class' => 'Cartino\\Database\\Seeders\\CartinoSeeder',
            ]);
            $this->info('Database seeded.');
        }

        // Create admin user
        if ($this->confirm('Would you like to create an admin user?')) {
            $this->createAdminUser();
        }

        $this->info('✅ Cartino has been installed successfully!');
        $this->info('');
        $this->info('Next steps:');
        $this->info('1. Configure your .env file with database settings');
        $this->info('2. Visit /admin to access the admin panel');
        $this->info('3. Check the documentation at: https://github.com/vitalijalbu/laravel-cartino');

        return self::SUCCESS;
    }

    private function createAdminUser(): void
    {
        $userModel = config('cartino.auth.model', 'App\\Models\\User');

        if (! class_exists($userModel)) {
            $this->error("User model {$userModel} not found. Please create it first.");

            return;
        }

        $name = $this->ask('Admin name', 'Admin');
        $email = $this->ask('Admin email', 'admin@admin.com');
        $password = $this->secret('Admin password');

        if (! $password) {
            $password = 'password';
            $this->info('Using default password: password');
        }

        $user = $userModel::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
            'email_verified_at' => now(),
        ]);

        // If Spatie Permissions is installed, assign admin role/permissions
        if (method_exists($user, 'givePermissionTo')) {
            try {
                $permissions = [
                    'manage-products',
                    'manage-orders',
                    'manage-customers',
                    'manage-collections',
                    'manage-brands',
                    'manage-discounts',
                    'manage-settings',
                ];

                foreach ($permissions as $permission) {
                    if (! Permission::where('name', $permission)->exists()) {
                        Permission::create(['name' => $permission]);
                    }
                }

                $user->givePermissionTo($permissions);
                $this->info('Admin permissions assigned.');
            } catch (\Exception $e) {
                $this->warn('Could not assign permissions: '.$e->getMessage());
            }
        }

        $this->info("Admin user created: {$email}");
    }
}
