<?php

namespace Cartino\Console\Commands;

use Cartino\Models\User;
use Illuminate\Console\Command;

class ShowAdminUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cartino:show-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show admin users with their credentials';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Searching for admin users...');

        $users = User::all();

        if ($users->isEmpty()) {
            $this->warn('❌ No users found in the database.');
            $this->line(
                '   Run the seeder first: php artisan db:seed --class=Cartino\\Database\\Seeders\\CartinoSeeder',
            );

            return 1;
        }

        $this->info('👥 Found users:');
        $this->newLine();

        $adminUsers = [];

        foreach ($users as $user) {
            $roles = [];

            if (method_exists($user, 'getRoleNames')) {
                $roles = $user->getRoleNames()->toArray();
            }

            $this->line("ID: {$user->id}");
            $this->line("Email: {$user->email}");
            $this->line("Name: {$user->first_name} {$user->last_name}");
            $this->line('Roles: '.(! empty($roles) ? implode(', ', $roles) : 'No roles'));
            $this->line("Created: {$user->created_at}");

            if (in_array('super-admin', $roles) || in_array('admin', $roles)) {
                $adminUsers[] = $user;
            }

            $this->newLine();
        }

        if (! empty($adminUsers)) {
            $this->info('🔑 Admin credentials (use these to login):');
            foreach ($adminUsers as $admin) {
                $roles = method_exists($admin, 'getRoleNames') ? $admin->getRoleNames()->toArray() : [];
                $this->line("Email: {$admin->email} | Password: password | Roles: ".implode(', ', $roles));
            }
        } else {
            $this->warn('⚠️  No admin users found. Run the seeder to create an admin user.');
        }

        return 0;
    }
}
