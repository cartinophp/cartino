<?php

declare(strict_types=1);

namespace Cartino\Workflows;

abstract class AbstractWorkflow implements WorkflowInterface
{
    protected bool $enabled = true;

    abstract public function getId(): string;

    abstract public function getName(): string;

    abstract public function getTrigger(): string;

    abstract public function getActions(): array;

    public function getDescription(): string
    {
        return '';
    }

    public function getConditions(): array
    {
        return [];
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Execute the workflow
     */
    public function execute(array $data): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        // Check conditions
        if (! $this->checkConditions($data)) {
            return false;
        }

        // Execute actions
        foreach ($this->getActions() as $action) {
            $this->executeAction($action, $data);
        }

        return true;
    }

    /**
     * Check if all conditions are met
     */
    protected function checkConditions(array $data): bool
    {
        foreach ($this->getConditions() as $condition) {
            if (! $this->evaluateCondition($condition, $data)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluate a single condition
     */
    protected function evaluateCondition(array $condition, array $data): bool
    {
        $field = $condition['field'];
        $operator = $condition['operator'];
        $value = $condition['value'];

        $fieldValue = data_get($data, $field);

        return match ($operator) {
            '=' => $fieldValue == $value,
            '!=' => $fieldValue != $value,
            '>' => $fieldValue > $value,
            '>=' => $fieldValue >= $value,
            '<' => $fieldValue < $value,
            '<=' => $fieldValue <= $value,
            'contains' => str_contains($fieldValue, $value),
            'not_contains' => ! str_contains($fieldValue, $value),
            'in' => in_array($fieldValue, (array) $value),
            'not_in' => ! in_array($fieldValue, (array) $value),
            default => false,
        };
    }

    /**
     * Execute a single action
     */
    protected function executeAction(array $action, array $data): void
    {
        $type = $action['type'];
        $config = $action['config'] ?? [];

        $actionClass = $this->getActionClass($type);

        if (class_exists($actionClass)) {
            $actionInstance = app($actionClass);
            $actionInstance->execute($data, $config);
        }
    }

    /**
     * Get action class name
     */
    protected function getActionClass(string $type): string
    {
        return 'Cartino\\Workflows\\Actions\\'.studly_case($type).'Action';
    }
}
