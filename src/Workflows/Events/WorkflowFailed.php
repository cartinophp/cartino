<?php

declare(strict_types=1);

namespace Cartino\Workflows\Events;

use Cartino\Workflows\WorkflowInterface;

class WorkflowFailed
{
    public function __construct(
        public WorkflowInterface $workflow,
        public array $data,
        public \Exception $exception,
    ) {}
}
