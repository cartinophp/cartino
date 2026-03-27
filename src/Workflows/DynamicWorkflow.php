<?php

declare(strict_types=1);

namespace Cartino\Workflows;

class DynamicWorkflow extends AbstractWorkflow
{
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
        $this->enabled = $model->is_active;
    }

    public function getId(): string
    {
        return $this->model->id;
    }

    public function getName(): string
    {
        return $this->model->name;
    }

    public function getDescription(): string
    {
        return $this->model->description ?? '';
    }

    public function getTrigger(): string
    {
        return $this->model->trigger;
    }

    public function getConditions(): array
    {
        return $this->model->conditions ?? [];
    }

    public function getActions(): array
    {
        return $this->model->actions ?? [];
    }
}
