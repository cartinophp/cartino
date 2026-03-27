<?php

declare(strict_types=1);

namespace ExamplePlugin;

use Cartino\Core\Plugin\AbstractPlugin;

class ExamplePlugin extends AbstractPlugin
{
    public function getId(): string
    {
        return 'example-plugin';
    }

    public function getName(): string
    {
        return 'Example Plugin';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getDescription(): string
    {
        return 'An example plugin demonstrating the plugin system';
    }

    public function getAuthor(): string
    {
        return 'Your Name';
    }

    public function getDependencies(): array
    {
        return [
            // 'other-plugin' => '>=1.0.0',
        ];
    }

    public function getConfigSchema(): array
    {
        return [
            'api_key' => [
                'type' => 'string',
                'required' => true,
                'label' => 'API Key',
                'description' => 'Your API key for the service',
            ],
            'enabled' => [
                'type' => 'boolean',
                'default' => true,
                'label' => 'Enable Feature',
            ],
        ];
    }

    public function boot(): void
    {
        parent::boot();

        // Add custom boot logic here
        // e.g., Register event listeners, views, routes
    }

    public function register(): void
    {
        parent::register();

        // Register custom services here
        // e.g., Bind interfaces to implementations
    }

    protected function registerEvents(): void
    {
        // Register event listeners
        // Event::listen(ProductCreated::class, SendProductNotification::class);
    }

    protected function registerCommands(): void
    {
        // Register artisan commands
        // $this->commands([
        //     ExampleCommand::class,
        // ]);
    }

    public function install(): void
    {
        parent::install();

        // Custom installation logic
        // e.g., Create default config, seed data
    }

    public function uninstall(): void
    {
        parent::uninstall();

        // Custom uninstallation logic
        // e.g., Remove plugin data
    }

    public function activate(): void
    {
        // Custom activation logic
    }

    public function deactivate(): void
    {
        // Custom deactivation logic
    }
}
