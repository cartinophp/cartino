<?php

namespace Cartino\Listeners;

abstract class Listener
{
    /**
     * Handle the event
     */
    abstract public function handle($event, $eventClass = null);

    /**
     * Register this listener for specific events
     */
    public static function subscribe($events = [])
    {
        $listener = new static;

        foreach ($events as $event => $priority) {
            if (is_numeric($event)) {
                $event = $priority;
                $priority = 0;
            }

            if (class_exists($event)) {
                $event::listen($event, [$listener, 'handle'], $priority);
            }
        }
    }

    /**
     * Log event activity
     */
    protected function log($message, $data = [])
    {
        // In a real implementation, this would use Laravel's logging
        error_log(json_encode([
            'listener' => static::class,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s'),
        ]));
    }

    /**
     * Send notification (placeholder)
     */
    protected function notify($message, $channels = ['database'])
    {
        // Placeholder for notification system
        $this->log("Notification: $message", compact('channels'));
    }

    /**
     * Update cache (placeholder)
     */
    protected function updateCache($key, $value = null)
    {
        // Placeholder for cache updates
        $this->log("Cache update: $key", compact('value'));
    }

    /**
     * Queue a job (placeholder)
     */
    protected function queue($job, $data = [])
    {
        // Placeholder for job queuing
        $this->log("Job queued: $job", $data);
    }
}
