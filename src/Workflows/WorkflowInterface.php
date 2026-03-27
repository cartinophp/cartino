<?php

declare(strict_types=1);

namespace Cartino\Workflows;

interface WorkflowInterface
{
    /**
     * Get workflow unique identifier
     */
    public function getId(): string;

    /**
     * Get workflow name
     */
    public function getName(): string;

    /**
     * Get workflow description
     */
    public function getDescription(): string;

    /**
     * Get workflow trigger event
     */
    public function getTrigger(): string;

    /**
     * Get workflow conditions
     */
    public function getConditions(): array;

    /**
     * Get workflow actions
     */
    public function getActions(): array;

    /**
     * Check if workflow is enabled
     */
    public function isEnabled(): bool;

    /**
     * Execute the workflow
     */
    public function execute(array $data): bool;
}
