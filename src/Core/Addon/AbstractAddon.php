<?php

declare(strict_types=1);

namespace Cartino\Core\Addon;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

abstract class AbstractAddon implements AddonInterface
{
    protected string $basePath;

    protected array $config = [];

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->loadConfig();
    }

    abstract public function getId(): string;

    abstract public function getName(): string;

    abstract public function getVersion(): string;

    public function getDescription(): string
    {
        return '';
    }

    public function getAuthor(): string
    {
        return '';
    }

    public function getDependencies(): array
    {
        return [];
    }

    public function getConfigSchema(): array
    {
        return [];
    }

    /**
     * Boot the addon - called after all addons are registered
     */
    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerViews();
        $this->registerTranslations();
        $this->registerAssets();
        $this->registerEvents();
    }

    /**
     * Register addon services - called during app registration
     */
    public function register(): void
    {
        $this->registerCommands();
        $this->registerMiddleware();
        $this->registerRepositories();
        $this->registerServices();
    }

    /**
     * Install the plugin
     */
    public function install(): void
    {
        $this->publishAssets();
        $this->runMigrations();
        $this->seedData();
    }

    /**
     * Uninstall the plugin
     */
    public function uninstall(): void
    {
        $this->rollbackMigrations();
        $this->removeData();
        $this->removeAssets();
    }

    /**
     * Activate the plugin
     */
    public function activate(): void
    {
        // Override if needed
    }

    /**
     * Deactivate the plugin
     */
    public function deactivate(): void
    {
        // Override if needed
    }

    /**
     * Update the plugin
     */
    public function update(string $fromVersion): void
    {
        $this->runMigrations();
    }

    /**
     * Get addon base path
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Get addon config
     */
    public function getConfig(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->config;
        }

        return data_get($this->config, $key, $default);
    }

    /**
     * Set addon config
     */
    public function setConfig(string $key, mixed $value): void
    {
        data_set($this->config, $key, $value);
        $this->saveConfig();
    }

    /**
     * Load addon configuration
     */
    protected function loadConfig(): void
    {
        $configPath = $this->basePath.'/config.json';

        if (File::exists($configPath)) {
            $this->config = json_decode(File::get($configPath), true) ?? [];
        }
    }

    /**
     * Save addon configuration
     */
    protected function saveConfig(): void
    {
        $configPath = $this->basePath.'/config.json';

        File::put($configPath, json_encode($this->config, JSON_PRETTY_PRINT));
    }

    /**
     * Register addon routes
     */
    protected function registerRoutes(): void
    {
        $routesPath = $this->basePath.'/routes';

        if (File::isDirectory($routesPath)) {
            if (File::exists($routesPath.'/web.php')) {
                require $routesPath.'/web.php';
            }

            if (File::exists($routesPath.'/api.php')) {
                require $routesPath.'/api.php';
            }
        }
    }

    /**
     * Register addon views
     */
    protected function registerViews(): void
    {
        $viewsPath = $this->basePath.'/resources/views';

        if (File::isDirectory($viewsPath)) {
            app('view')->addNamespace($this->getId(), $viewsPath);
        }
    }

    /**
     * Register addon translations
     */
    protected function registerTranslations(): void
    {
        $langPath = $this->basePath.'/resources/lang';

        if (File::isDirectory($langPath)) {
            app('translator')->addNamespace($this->getId(), $langPath);
        }
    }

    /**
     * Register addon assets
     */
    protected function registerAssets(): void
    {
        // Assets are published, not registered at runtime
    }

    /**
     * Register addon events
     */
    protected function registerEvents(): void
    {
        // Override in addon to register event listeners
    }

    /**
     * Register addon commands
     */
    protected function registerCommands(): void
    {
        // Override in addon to register artisan commands
    }

    /**
     * Register addon middleware
     */
    protected function registerMiddleware(): void
    {
        // Override in addon to register middleware
    }

    /**
     * Register addon repositories
     */
    protected function registerRepositories(): void
    {
        // Override in addon to bind repositories
    }

    /**
     * Register addon services
     */
    protected function registerServices(): void
    {
        // Override in addon to bind services
    }

    /**
     * Publish addon assets
     */
    protected function publishAssets(): void
    {
        $publicPath = $this->basePath.'/public';

        if (File::isDirectory($publicPath)) {
            File::copyDirectory($publicPath, public_path('addons/'.$this->getId()));
        }
    }

    /**
     * Run addon migrations
     */
    protected function runMigrations(): void
    {
        $migrationsPath = $this->basePath.'/database/migrations';

        if (File::isDirectory($migrationsPath)) {
            Artisan::call('migrate', [
                '--path' => str_replace(base_path().'/', '', $migrationsPath),
                '--force' => true,
            ]);
        }
    }

    /**
     * Rollback addon migrations
     */
    protected function rollbackMigrations(): void
    {
        $migrationsPath = $this->basePath.'/database/migrations';

        if (File::isDirectory($migrationsPath)) {
            Artisan::call('migrate:rollback', [
                '--path' => str_replace(base_path().'/', '', $migrationsPath),
                '--force' => true,
            ]);
        }
    }

    /**
     * Seed addon data
     */
    protected function seedData(): void
    {
        // Override in addon to seed data
    }

    /**
     * Remove addon data
     */
    protected function removeData(): void
    {
        // Override in addon to remove data
    }

    /**
     * Remove addon assets
     */
    protected function removeAssets(): void
    {
        File::deleteDirectory(public_path('addons/'.$this->getId()));
    }
}
