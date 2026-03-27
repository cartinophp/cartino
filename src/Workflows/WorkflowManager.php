<?php

declare(strict_types=1);

namespace Cartino\Workflows;

use Cartino\Models\Workflow;
use Cartino\Models\WorkflowLog;
use Cartino\Workflows\Events\WorkflowExecuted;
use Cartino\Workflows\Events\WorkflowFailed;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;

class WorkflowManager
{
    protected Collection $workflows;

    public function __construct()
    {
        $this->workflows = collect();
        $this->loadWorkflows();
        $this->registerEventListeners();
    }

    /**
     * Load all workflows
     */
    protected function loadWorkflows(): void
    {
        // Load workflows from database
        $workflows = Workflow::where('is_active', true)->get();

        foreach ($workflows as $workflowModel) {
            $workflow = $this->instantiateWorkflow($workflowModel);

            if ($workflow) {
                $this->workflows->put($workflow->getId(), $workflow);
            }
        }
    }

    /**
     * Instantiate workflow from model
     */
    protected function instantiateWorkflow($model): ?WorkflowInterface
    {
        if ($model->type === 'custom' && class_exists($model->class)) {
            return new $model->class($model);
        }

        // Create dynamic workflow from configuration
        return new DynamicWorkflow($model);
    }

    /**
     * Register event listeners for workflows
     */
    protected function registerEventListeners(): void
    {
        foreach ($this->workflows as $workflow) {
            Event::listen($workflow->getTrigger(), function ($event) use ($workflow) {
                $this->executeWorkflow($workflow, $event);
            });
        }
    }

    /**
     * Execute a workflow
     */
    public function executeWorkflow(WorkflowInterface $workflow, $event): void
    {
        try {
            $data = is_array($event) ? $event : ((array) $event);

            $result = $workflow->execute($data);

            event(new WorkflowExecuted($workflow, $data, $result));

            $this->logWorkflowExecution($workflow, $data, $result);
        } catch (\Exception $e) {
            event(new WorkflowFailed($workflow, $data ?? [], $e));

            $this->logWorkflowFailure($workflow, $data ?? [], $e);
        }
    }

    /**
     * Get all workflows
     */
    public function all(): Collection
    {
        return $this->workflows;
    }

    /**
     * Get workflows by trigger
     */
    public function getByTrigger(string $trigger): Collection
    {
        return $this->workflows->filter(function ($workflow) use ($trigger) {
            return $workflow->getTrigger() === $trigger;
        });
    }

    /**
     * Register a workflow
     */
    public function register(WorkflowInterface $workflow): void
    {
        $this->workflows->put($workflow->getId(), $workflow);

        Event::listen($workflow->getTrigger(), function ($event) use ($workflow) {
            $this->executeWorkflow($workflow, $event);
        });
    }

    /**
     * Unregister a workflow
     */
    public function unregister(string $id): void
    {
        $this->workflows->forget($id);
    }

    /**
     * Log workflow execution
     */
    protected function logWorkflowExecution(WorkflowInterface $workflow, array $data, bool $result): void
    {
        WorkflowLog::create([
            'workflow_id' => $workflow->getId(),
            'trigger' => $workflow->getTrigger(),
            'data' => $data,
            'result' => $result ? 'success' : 'skipped',
            'executed_at' => now(),
        ]);
    }

    /**
     * Log workflow failure
     */
    protected function logWorkflowFailure(WorkflowInterface $workflow, array $data, \Exception $exception): void
    {
        WorkflowLog::create([
            'workflow_id' => $workflow->getId(),
            'trigger' => $workflow->getTrigger(),
            'data' => $data,
            'result' => 'failed',
            'error_message' => $exception->getMessage(),
            'error_trace' => $exception->getTraceAsString(),
            'executed_at' => now(),
        ]);
    }
}
