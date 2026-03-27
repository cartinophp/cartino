<?php

declare(strict_types=1);

namespace Cartino\Support\Traits;

trait FluentlyGetsAndSets
{
    protected $fluentPropertyName;

    protected $fluentGetter;

    protected $fluentSetter;

    protected $fluentArgs;

    protected function fluentlyGetOrSet($property)
    {
        $this->fluentPropertyName = $property;
        $this->fluentGetter = null;
        $this->fluentSetter = null;
        $this->fluentArgs = null;

        return $this;
    }

    protected function getter($callback)
    {
        $this->fluentGetter = $callback;

        return $this;
    }

    protected function setter($callback)
    {
        $this->fluentSetter = $callback;

        return $this;
    }

    protected function args($args)
    {
        $this->fluentArgs = $args;

        return $this->fluentlyExecute();
    }

    protected function fluentlyExecute()
    {
        $property = $this->fluentPropertyName;

        // If no arguments provided, it's a getter
        if (empty($this->fluentArgs)) {
            $value = $this->{$property} ?? null;

            if ($this->fluentGetter) {
                return call_user_func($this->fluentGetter, $value);
            }

            return $value;
        }

        // If argument provided, it's a setter
        $value = $this->fluentArgs[0];

        if ($this->fluentSetter) {
            $value = call_user_func($this->fluentSetter, $value);
        }

        $this->{$property} = $value;

        return $this;
    }
}
