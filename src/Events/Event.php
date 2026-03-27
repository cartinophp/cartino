<?php

namespace Cartino\Events;

abstract class Event
{
    protected $data = [];

    protected static $listeners = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function get($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Fire/emit this event
     */
    public function fire()
    {
        return static::emit(static::class, $this->data);
    }

    /**
     * Static method to emit an event
     */
    public static function emit($event, $data = [])
    {
        if (is_object($event)) {
            $eventClass = get_class($event);
            $eventData = $event->getData();
        } else {
            $eventClass = $event;
            $eventData = $data;
        }

        $listeners = static::getListeners($eventClass);
        $results = [];

        foreach ($listeners as $listener) {
            $result = call_user_func($listener, $eventData, $eventClass);
            if ($result !== null) {
                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * Register an event listener
     */
    public static function listen($event, $listener, $priority = 0)
    {
        if (! isset(static::$listeners[$event])) {
            static::$listeners[$event] = [];
        }

        static::$listeners[$event][] = [
            'listener' => $listener,
            'priority' => $priority,
        ];

        // Sort by priority (higher first)
        usort(static::$listeners[$event], function ($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });
    }

    /**
     * Get listeners for an event
     */
    public static function getListeners($event)
    {
        if (! isset(static::$listeners[$event])) {
            return [];
        }

        return array_column(static::$listeners[$event], 'listener');
    }

    /**
     * Remove all listeners for an event
     */
    public static function clearListeners($event = null)
    {
        if ($event) {
            unset(static::$listeners[$event]);
        } else {
            static::$listeners = [];
        }
    }

    /**
     * Check if event has listeners
     */
    public static function hasListeners($event)
    {
        return isset(static::$listeners[$event]) && ! empty(static::$listeners[$event]);
    }

    /**
     * Get event name for logging/debugging
     */
    public function getName()
    {
        return static::class;
    }

    /**
     * Convert to array
     */
    public function toArray()
    {
        return [
            'event' => $this->getName(),
            'data' => $this->getData(),
            'timestamp' => time(),
        ];
    }
}
