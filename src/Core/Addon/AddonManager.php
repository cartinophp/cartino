<?php

declare(strict_types=1);

namespace Cartino\Core\Addon;

use Cartino\Core\Addon\Events\AddonUninstalled;
use Cartino\Core\Addon\Events\PluginActivated;
use Cartino\Core\Addon\Events\PluginDeactivated;
use Cartino\Core\Addon\Events\PluginInstalled;
use Cartino\Core\Addon\Events\PluginUpdated;
use Cartino\Core\Addon\Exceptions\AddonException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class AddonManager
{
    protected Collection $addons;

    protected Collection $activePlugins;

    protected string $addonsPath;

    protected AddonRepository $repository;

    public function __construct(AddonRepository $repository)
    {
        $this->repository = $repository;
        $this->addons = collect();
        $this->activePlugins = collect();
        $this->addonsPath = base_path('addons');

        $this->loadPlugins();
    }

    /**
     * Load all available addons
     */
    protected function loadPlugins(): void
    {
        if (! File::isDirectory($this->addonsPath)) {
            File::makeDirectory($this->addonsPath, 0755, true);

            return;
        }

        $directories = File::directories($this->addonsPath);

        foreach ($directories as $directory) {
            $this->loadPlugin($directory);
        }
    }

    /**
     * Load a single plugin
     */
    protected function loadPlugin(string $path): void
    {
        $manifestPath = $path.'/plugin.json';

        if (! File::exists($manifestPath)) {
            return;
        }

        $manifest = json_decode(File::get($manifestPath), true);

        if (! isset($manifest['class'])) {
            return;
        }

        $className = $manifest['class'];

        if (! class_exists($className)) {
            // Try to load via composer autoload from addon directory
            $autoloadPath = $path.'/vendor/autoload.php';
            if (File::exists($autoloadPath)) {
                require_once $autoloadPath;
            }
        }

        if (! class_exists($className)) {
            return;
        }

        try {
            $addon = new $className($path);

            if (! ($addon instanceof AddonInterface)) {
                return;
            }

            $this->addons->put($addon->getId(), $addon);

            // Check if addon is active in database
            if ($this->repository->isActive($addon->getId())) {
                $this->activePlugins->put($addon->getId(), $addon);
            }
        } catch (\Exception $e) {
            logger()->error("Failed to load addon from {$path}: ".$e->getMessage());
        }
    }

    /**
     * Get all addons
     */
    public function all(): Collection
    {
        return $this->addons;
    }

    /**
     * Get active addons
     */
    public function active(): Collection
    {
        return $this->activePlugins;
    }

    /**
     * Get addon by ID
     */
    public function get(string $id): ?AddonInterface
    {
        return $this->addons->get($id);
    }

    /**
     * Check if addon exists
     */
    public function has(string $id): bool
    {
        return $this->addons->has($id);
    }

    /**
     * Check if addon is active
     */
    public function isActive(string $id): bool
    {
        return $this->activePlugins->has($id);
    }

    /**
     * Install a plugin
     */
    public function install(string $id): void
    {
        $addon = $this->get($id);

        if (! $addon) {
            throw new AddonException("Plugin {$id} not found");
        }

        // Check dependencies
        $this->checkDependencies($addon);

        // Install plugin
        $addon->install();

        // Save to database
        $this->repository->create([
            'id' => $addon->getId(),
            'name' => $addon->getName(),
            'version' => $addon->getVersion(),
            'is_active' => false,
        ]);

        event(new PluginInstalled($addon));

        Cache::tags(['addons'])->flush();
    }

    /**
     * Uninstall a plugin
     */
    public function uninstall(string $id): void
    {
        $addon = $this->get($id);

        if (! $addon) {
            throw new AddonException("Plugin {$id} not found");
        }

        // Check if other addons depend on this one
        $dependents = $this->getDependents($id);

        if ($dependents->isNotEmpty()) {
            throw new AddonException(
                "Cannot uninstall {$id} because the following addons depend on it: ".
                    $dependents->pluck('name')->implode(', '),
            );
        }

        // Deactivate first if active
        if ($this->isActive($id)) {
            $this->deactivate($id);
        }

        // Uninstall plugin
        $addon->uninstall();

        // Remove from database
        $this->repository->delete($id);

        event(new AddonUninstalled($addon));

        Cache::tags(['addons'])->flush();
    }

    /**
     * Activate a plugin
     */
    public function activate(string $id): void
    {
        $addon = $this->get($id);

        if (! $addon) {
            throw new AddonException("Plugin {$id} not found");
        }

        if ($this->isActive($id)) {
            return;
        }

        // Check if addon is installed
        if (! $this->repository->exists($id)) {
            throw new AddonException("Plugin {$id} is not installed");
        }

        // Check dependencies are active
        foreach ($addon->getDependencies() as $dependencyId => $version) {
            if (! $this->isActive($dependencyId)) {
                throw new AddonException("Plugin {$id} requires {$dependencyId} to be active");
            }
        }

        // Activate plugin
        $addon->activate();
        $addon->register();
        $addon->boot();

        // Update database
        $this->repository->activate($id);

        // Add to active addons
        $this->activePlugins->put($id, $addon);

        event(new PluginActivated($addon));

        Cache::tags(['addons'])->flush();
    }

    /**
     * Deactivate a plugin
     */
    public function deactivate(string $id): void
    {
        $addon = $this->get($id);

        if (! $addon) {
            throw new AddonException("Plugin {$id} not found");
        }

        if (! $this->isActive($id)) {
            return;
        }

        // Check if other active addons depend on this one
        $activeDependents = $this->getActiveDependents($id);

        if ($activeDependents->isNotEmpty()) {
            throw new AddonException(
                "Cannot deactivate {$id} because the following active addons depend on it: ".
                    $activeDependents->pluck('name')->implode(', '),
            );
        }

        // Deactivate plugin
        $addon->deactivate();

        // Update database
        $this->repository->deactivate($id);

        // Remove from active addons
        $this->activePlugins->forget($id);

        event(new PluginDeactivated($addon));

        Cache::tags(['addons'])->flush();
    }

    /**
     * Update a plugin
     */
    public function update(string $id): void
    {
        $addon = $this->get($id);

        if (! $addon) {
            throw new AddonException("Plugin {$id} not found");
        }

        $oldVersion = $this->repository->getVersion($id);
        $newVersion = $addon->getVersion();

        if (version_compare($newVersion, $oldVersion, '<=')) {
            return;
        }

        // Update plugin
        $addon->update($oldVersion);

        // Update database
        $this->repository->updateVersion($id, $newVersion);

        event(new PluginUpdated($addon, $oldVersion, $newVersion));

        Cache::tags(['addons'])->flush();
    }

    /**
     * Register all active addons
     */
    public function registerActive(): void
    {
        foreach ($this->activePlugins as $addon) {
            $addon->register();
        }
    }

    /**
     * Boot all active addons
     */
    public function bootActive(): void
    {
        foreach ($this->activePlugins as $addon) {
            $addon->boot();
        }
    }

    /**
     * Check addon dependencies
     */
    protected function checkDependencies(AddonInterface $addon): void
    {
        foreach ($addon->getDependencies() as $dependencyId => $versionConstraint) {
            $dependency = $this->get($dependencyId);

            if (! $dependency) {
                throw new AddonException("Plugin {$addon->getId()} requires {$dependencyId} but it's not available");
            }

            if (! $this->repository->exists($dependencyId)) {
                throw new AddonException("Plugin {$addon->getId()} requires {$dependencyId} to be installed");
            }

            $installedVersion = $dependency->getVersion();

            if (! $this->versionSatisfies($installedVersion, $versionConstraint)) {
                throw new AddonException(
                    "Plugin {$addon->getId()} requires {$dependencyId} {$versionConstraint} ".
                        "but version {$installedVersion} is installed",
                );
            }
        }
    }

    /**
     * Get addons that depend on the given plugin
     */
    protected function getDependents(string $id): Collection
    {
        return $this->addons->filter(function (AddonInterface $addon) use ($id) {
            return array_key_exists($id, $addon->getDependencies());
        });
    }

    /**
     * Get active addons that depend on the given plugin
     */
    protected function getActiveDependents(string $id): Collection
    {
        return $this->activePlugins->filter(function (AddonInterface $addon) use ($id) {
            return array_key_exists($id, $addon->getDependencies());
        });
    }

    /**
     * Check if version satisfies constraint
     */
    protected function versionSatisfies(string $version, string $constraint): bool
    {
        // Simple version comparison - you can use composer/semver for complex constraints
        if (str_starts_with($constraint, '>=')) {
            return version_compare($version, substr($constraint, 2), '>=');
        }

        if (str_starts_with($constraint, '>')) {
            return version_compare($version, substr($constraint, 1), '>');
        }

        if (str_starts_with($constraint, '<=')) {
            return version_compare($version, substr($constraint, 2), '<=');
        }

        if (str_starts_with($constraint, '<')) {
            return version_compare($version, substr($constraint, 1), '<');
        }

        if (str_starts_with($constraint, '^')) {
            // Compatible versions: ^1.2.3 means >=1.2.3 <2.0.0
            $minVersion = substr($constraint, 1);
            $parts = explode('.', $minVersion);
            $maxVersion = ($parts[0] + 1).'.0.0';

            return version_compare($version, $minVersion, '>=') && version_compare($version, $maxVersion, '<');
        }

        return version_compare($version, $constraint, '=');
    }
}
