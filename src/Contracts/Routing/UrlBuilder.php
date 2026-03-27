<?php

declare(strict_types=1);

namespace Cartino\Contracts\Routing;

class UrlBuilder
{
    protected $content;

    protected $params = [];

    protected $route;

    /**
     * Set the content being URL'd
     *
     * @param  mixed  $content
     * @return $this
     */
    public function content($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Merge parameters for the URL
     *
     * @return $this
     */
    public function merge(array $params)
    {
        $this->params = array_merge($this->params, $params);

        return $this;
    }

    /**
     * Build the URL from the route
     *
     * @param  string  $route
     * @return string
     */
    public function build($route)
    {
        $this->route = $route;

        // Replace route parameters with values
        $url = $route;

        foreach ($this->params as $key => $value) {
            $url = str_replace('{'.$key.'}', (string) $value, $url);
        }

        // Remove any remaining unreplaced parameters
        $url = preg_replace('/\{[^}]+\}/', '', $url);

        // Clean up double slashes (except after protocol)
        $url = preg_replace('#(?<!:)//+#', '/', $url);

        return rtrim($url, '/');
    }

    /**
     * Get the current content
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get the params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
}
