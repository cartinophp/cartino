<?php

declare(strict_types=1);

namespace Cartino\Core\Addon;

interface AddonInterface
{
    /**
     * Get addon unique identifier
     */
    public function getId(): string;

    /**
     * Get addon name
     */
    public function getName(): string;

    /**
     * Get addon version
     */
    public function getVersion(): string;

    /**
     * Get addon description
     */
    public function getDescription(): string;

    /**
     * Get addon author
     */
    public function getAuthor(): string;

    /**
     * Get addon dependencies
     *
     * @return array<string, string> ['addon-id' => '>=1.0.0']
     */
    public function getDependencies(): array;

    /**
     * Get addon configuration schema
     */
    public function getConfigSchema(): array;

    /**
     * Boot the addon
     */
    public function boot(): void;

    /**
     * Register addon services
     */
    public function register(): void;

    /**
     * Install the addon
     */
    public function install(): void;

    /**
     * Uninstall the addon
     */
    public function uninstall(): void;

    /**
     * Activate the addon
     */
    public function activate(): void;

    /**
     * Deactivate the addon
     */
    public function deactivate(): void;

    /**
     * Update the addon from old version
     */
    public function update(string $fromVersion): void;
}
