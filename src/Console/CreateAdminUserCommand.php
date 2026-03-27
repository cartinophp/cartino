<?php

namespace Cartino\Console;

use Cartino\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cartino:admin 
                            {--email= : Admin email address}
                            {--password= : Admin password}
                            {--name= : Admin name}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new admin user with super-admin role for Shopper Control Panel';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Creating Shopper Super Admin User...');
        $this->info('This user will have full access to the Control Panel.');
        $this->newLine();

        // Get user input
        $name = $this->option('name') ?: $this->ask('What is the admin name?', 'Admin User');
        $email = $this->option('email') ?: $this->ask('What is the admin email?');
        $password = $this->option('password') ?: $this->secret('What is the admin password?');

        // Validate input
        $validator = Validator::make(
            [
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ],
            [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
            ],
        );

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->error('  - '.$error);
            }

            return Command::FAILURE;
        }

        try {
            // Check if user already exists
            if (User::where('email', $email)->exists()) {
                $this->error("User with email '{$email}' already exists!");

                return Command::FAILURE;
            }

            // Create the admin user
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);

            // Assign super-admin role if Spatie roles/permissions are available
            if (method_exists($user, 'assignRole')) {
                try {
                    // Try to assign super-admin role first
                    $user->assignRole('super-admin');
                    $this->info('✅ Assigned super-admin role to user.');
                } catch (\Exception $e) {
                    // Fallback to admin role if super-admin doesn't exist
                    try {
                        $user->assignRole('admin');
                        $this->info('✅ Assigned admin role to user.');
                    } catch (\Exception $e2) {
                        $this->warn('⚠️  Could not assign any role. You may need to run database seeders first.');
                        $this->info('💡 Run: php artisan db:seed --class=CartinoSeeder');
                    }
                }
            } else {
                // Fallback: set a flag for CP access if roles aren't available
                $user->update(['can_access_cp' => true]);
                $this->info('✅ Granted Control Panel access.');
            }

            $this->newLine();
            $this->info('✅ Admin user created successfully!');

            // Get user's roles for display
            $roles = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->implode(', ') : 'None';

            $this->table(
                ['Field', 'Value'],
                [
                    ['Name', $user->name],
                    ['Email', $user->email],
                    ['ID', $user->id],
                    ['Roles', $roles ?: 'CP Access Granted'],
                    ['Created', $user->created_at->format('Y-m-d H:i:s')],
                ],
            );

            $this->newLine();
            $this->info('🎉 You can now login to the Control Panel with these credentials.');

            if (! $roles) {
                $this->info('💡 To enable full role-based permissions, run: php artisan db:seed --class=CartinoSeeder');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error creating admin user: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
