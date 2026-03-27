<?php

namespace Cartino\Http\Middleware;

use Cartino\Models\Category;
use Cartino\Models\Product;
use Cartino\Models\Site;
use Cartino\Services\TemplateEngine;
use Closure;
use Illuminate\Http\Request;

class StorefrontTemplateMiddleware
{
    protected TemplateEngine $templateEngine;

    public function __construct(TemplateEngine $templateEngine)
    {
        $this->templateEngine = $templateEngine;
    }

    public function handle(Request $request, Closure $next)
    {
        // Skip API routes
        if ($request->is('api/*')) {
            return $next($request);
        }

        // Set current site in container
        $site = $this->resolveSite($request);
        app()->singleton('laravel-cartino.site', fn () => $site);

        // Auto-assign templates based on route
        $this->assignTemplateForRoute($request);

        return $next($request);
    }

    protected function resolveSite(Request $request): Site
    {
        // Multi-site detection logic
        $host = $request->getHost();
        $subdomain = $this->extractSubdomain($host);

        // Try subdomain first
        if ($subdomain) {
            $site = Site::where('handle', $subdomain)->active()->first();
            if ($site) {
                return $site;
            }
        }

        // Try domain
        $site = Site::where('domain', $host)->active()->first();
        if ($site) {
            return $site;
        }

        // Fallback to default site
        return Site::where('is_default', true)->active()->firstOrFail();
    }

    protected function extractSubdomain(string $host): ?string
    {
        $parts = explode('.', $host);

        return count($parts) > 2 ? $parts[0] : null;
    }

    protected function assignTemplateForRoute(Request $request): void
    {
        $route = $request->route();

        if (! $route) {
            return;
        }

        $routeName = $route->getName();
        $parameters = $route->parameters();

        // Map routes to template types
        $templateMappings = [
            'storefront.home' => 'index',
            'storefront.products.show' => 'product',
            'storefront.categories.show' => 'collection',
            'storefront.pages.show' => 'page',
            'storefront.blog.index' => 'blog',
            'storefront.blog.show' => 'article',
        ];

        $templateType = $templateMappings[$routeName] ?? null;

        if ($templateType) {
            $resource = $this->getResourceFromRoute($route, $parameters);
            $customTemplate = $this->getCustomTemplate($request, $resource);

            // Store template info in request for controllers
            $request->attributes->set('template_type', $templateType);
            $request->attributes->set('template_resource', $resource);
            $request->attributes->set('custom_template', $customTemplate);
        }
    }

    protected function getResourceFromRoute($route, array $parameters)
    {
        $routeName = $route->getName();

        return match ($routeName) {
            'storefront.products.show' => Product::where(
                'handle',
                $parameters['handle'] ?? $parameters['product'] ?? null,
            )->first(),
            'storefront.categories.show' => Category::where(
                'handle',
                $parameters['handle'] ?? $parameters['category'] ?? null,
            )->first(),
            default => null,
        };
    }

    protected function getCustomTemplate(Request $request, $resource): ?string
    {
        // Check query parameter
        if ($request->has('template')) {
            return $request->get('template');
        }

        // Check resource template assignment
        if ($resource && method_exists($resource, 'getTemplateHandle')) {
            return $resource->getTemplateHandle();
        }

        return null;
    }
}
