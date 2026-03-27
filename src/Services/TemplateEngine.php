<?php

namespace Cartino\Services;

use Cartino\Models\Site;
use Cartino\Models\StorefrontTemplate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;

class TemplateEngine
{
    protected Site $currentSite;

    public function __construct()
    {
        $this->currentSite = app('laravel-cartino.site');
    }

    /**
     * Render a resource with appropriate template
     */
    public function render(string $resourceType, mixed $resource = null, ?string $customTemplate = null): string
    {
        $template = $this->resolveTemplate($resourceType, $resource, $customTemplate);

        if (! $template) {
            throw new \Exception("No template found for {$resourceType}");
        }

        $compiledData = $template->compile();
        $globalSettings = $this->getGlobalSettings();

        return View::make('cartino::storefront.template', [
            'template' => $compiledData,
            'resource' => $resource,
            'site' => $this->currentSite,
            'settings' => $globalSettings,
        ])->render();
    }

    /**
     * Resolve the appropriate template for a resource
     */
    protected function resolveTemplate(
        string $resourceType,
        mixed $resource = null,
        ?string $customTemplate = null,
    ): ?StorefrontTemplate {
        // 1. Custom template specified
        if ($customTemplate) {
            $template = StorefrontTemplate::where('site_id', $this->currentSite->id)
                ->where('handle', $customTemplate)
                ->active()
                ->first();

            if ($template && $template->canAssignTo($resourceType)) {
                return $template;
            }
        }

        // 2. Resource-specific template (e.g., product has template_handle)
        if ($resource && method_exists($resource, 'getTemplateHandle')) {
            $templateHandle = $resource->getTemplateHandle();

            if ($templateHandle) {
                $template = StorefrontTemplate::where('site_id', $this->currentSite->id)
                    ->where('handle', $templateHandle)
                    ->active()
                    ->first();

                if ($template) {
                    return $template;
                }
            }
        }

        // 3. Category/Category specific template
        if ($resource && method_exists($resource, 'getCategory')) {
            $category = $resource->getCategory();

            if ($category && $category->template_handle) {
                $template = StorefrontTemplate::where('site_id', $this->currentSite->id)
                    ->where('handle', $category->template_handle)
                    ->active()
                    ->first();

                if ($template) {
                    return $template;
                }
            }
        }

        // 4. Default template for resource type
        return StorefrontTemplate::where('site_id', $this->currentSite->id)
            ->where('type', $resourceType)
            ->where('is_default', true)
            ->active()
            ->first();
    }

    /**
     * Get global theme settings
     */
    protected function getGlobalSettings(): array
    {
        $themeSettings = $this->currentSite
            ->themeSettings()
            ->where('is_active', true)
            ->first();

        return $themeSettings
            ? [
                'global' => $themeSettings->global_settings ?? [],
                'navigation' => $themeSettings->navigation_menus ?? [],
                'social' => $themeSettings->social_links ?? [],
                'seo' => $themeSettings->seo_settings ?? [],
                'custom_css' => $themeSettings->custom_css ?? [],
                'custom_js' => $themeSettings->custom_js ?? [],
            ] : [];
    }

    /**
     * Get available templates for a resource type
     */
    public function getAvailableTemplates(string $resourceType): Collection
    {
        return StorefrontTemplate::where('site_id', $this->currentSite->id)
            ->where('type', $resourceType)
            ->active()
            ->orderBy('name')
            ->get();
    }

    /**
     * Preview a template with sample data
     */
    public function preview(string $templateHandle, array $sampleData = []): string
    {
        $template = StorefrontTemplate::where('site_id', $this->currentSite->id)
            ->where('handle', $templateHandle)
            ->active()
            ->firstOrFail();

        $compiledData = $template->compile();
        $globalSettings = $this->getGlobalSettings();

        return View::make('cartino::storefront.template', [
            'template' => $compiledData,
            'resource' => (object) $sampleData,
            'site' => $this->currentSite,
            'settings' => $globalSettings,
            'preview_mode' => true,
        ])->render();
    }
}
