<?php

namespace Cartino\Http\Controllers\Cp;

use Cartino\Cp\Page;
use Cartino\DTO\PageDto;
use Cartino\Http\Controllers\Controller;
use Cartino\Models\Page as ShopperPage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PagesController extends Controller
{
    /**
     * Pages index
     */
    public function index(Request $request)
    {
        $page = Page::make('Pages')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Content', '/cp/content')
            ->breadcrumb('Pages')
            ->primaryAction('Add page', '/cp/pages/create')
            ->secondaryActions([
                ['label' => 'Import', 'url' => '/cp/pages/import'],
                ['label' => 'Export', 'url' => '/cp/pages/export'],
            ]);

        $pages = ShopperPage::select([
            'id',
            'title',
            'handle',
            'status',
            'show_title',
            'seo_title',
            'seo_description',
            'published_at',
            'author_id',
            'template_id',
            'created_at',
            'updated_at',
        ])
            ->with(['author:id,name', 'template:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Pages/index', [
            'page' => $page->compile(),
            'pages' => $pages,
        ]);
    }

    /**
     * Create page
     */
    public function create()
    {
        $page = Page::make('Add page')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Content', '/cp/content')
            ->breadcrumb('Pages', '/cp/pages')
            ->breadcrumb('Add page')
            ->backUrl('/cp/pages')
            ->primaryAction('Save page', null, ['form' => 'page-form'])
            ->secondaryActions([
                ['label' => 'Save & continue editing', 'action' => 'save_continue'],
                ['label' => 'Save & add another', 'action' => 'save_add_another'],
            ]);

        return Inertia::render('Pages/Create', [
            'page' => $page->compile(),
            'templates' => $this->getAvailableTemplates(),
        ]);
    }

    /**
     * Store page using DTO
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:published,draft,private',
            'template_id' => 'nullable|exists:shopper_templates,id',
            'show_title' => 'boolean',
            'seo_title' => 'nullable|string|max:60',
            'seo_description' => 'nullable|string|max:160',
            'published_at' => 'nullable|date',
        ]);

        // Create DTO from validated data
        $pageDto = PageDto::from(array_merge($validated, [
            'site_id' => app('laravel-cartino.site')->id,
            'author_id' => auth()->id(),
        ]));

        // Additional DTO validation
        $dtoErrors = $pageDto->validate();
        if (! empty($dtoErrors)) {
            return response()->json(['errors' => $dtoErrors], 422);
        }

        // Create page from DTO
        $page = ShopperPage::create($pageDto->toArray());

        // Handle different save actions
        $action = $request->input('_action', 'save');

        return match ($action) {
            'save_continue' => response()->json([
                'message' => 'Page created successfully',
                'redirect' => "/cp/pages/{$page->id}/edit",
            ]),
            'save_add_another' => response()->json([
                'message' => 'Page created successfully',
                'redirect' => '/cp/pages/create',
            ]),
            default => response()->json([
                'message' => 'Page created successfully',
                'redirect' => '/cp/pages',
            ]),
        };
    }

    /**
     * Show/edit page
     */
    public function show(ShopperPage $page)
    {
        $pageBuilder = Page::make($page->title)
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Content', '/cp/content')
            ->breadcrumb('Pages', '/cp/pages')
            ->breadcrumb($page->title)
            ->backUrl('/cp/pages')
            ->primaryAction('Save', null, ['form' => 'page-form'])
            ->secondaryActions([
                ['label' => 'View page', 'url' => "/pages/{$page->handle}", 'external' => true],
                ['label' => 'Page builder', 'url' => "/cp/pages/{$page->id}/builder"],
                ['label' => 'Duplicate', 'url' => "/cp/pages/{$page->id}/duplicate"],
                ['label' => 'Delete', 'url' => '#', 'destructive' => true],
            ])
            ->tabs([
                'content' => ['label' => 'Content', 'component' => 'PageContentForm'],
                'settings' => ['label' => 'Settings', 'component' => 'PageSettingsForm'],
                'seo' => ['label' => 'SEO', 'component' => 'PageSeoForm'],
            ]);

        return Inertia::render('Pages/Edit', [
            'page' => $pageBuilder->compile(),
            'pageData' => $page,
            'templates' => $this->getAvailableTemplates(),
        ]);
    }

    /**
     * Update page using DTO
     */
    public function update(Request $request, ShopperPage $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:published,draft,private',
            'template_id' => 'nullable|exists:shopper_templates,id',
            'show_title' => 'boolean',
            'seo_title' => 'nullable|string|max:60',
            'seo_description' => 'nullable|string|max:160',
            'published_at' => 'nullable|date',
        ]);

        // Create DTO from validated data
        $pageDto = PageDto::from($validated);

        // Additional DTO validation
        $dtoErrors = $pageDto->validate();
        if (! empty($dtoErrors)) {
            return response()->json(['errors' => $dtoErrors], 422);
        }

        // Update page from DTO
        $page->update($pageDto->toArray());

        return response()->json([
            'message' => 'Page updated successfully',
            'page' => $page->fresh(),
        ]);
    }

    /**
     * Delete page
     */
    public function destroy(ShopperPage $page)
    {
        $page->delete();

        return response()->json([
            'message' => 'Page deleted successfully',
        ]);
    }

    /**
     * Page builder interface
     */
    public function builder(Request $request, ?ShopperPage $page = null)
    {
        $pageBuilder = Page::make($page ? "Edit {$page->title}" : 'Page Builder')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Content', '/cp/content')
            ->breadcrumb('Pages', '/cp/pages')
            ->breadcrumb($page ? $page->title : 'Page Builder')
            ->backUrl('/cp/pages')
            ->primaryAction('Save', null, ['form' => 'page-builder-form'])
            ->secondaryActions([
                [
                    'label' => 'Preview',
                    'url' => $page ? "/pages/{$page->handle}?preview=true" : '#',
                    'external' => true,
                ],
                ['label' => 'Publish', 'action' => 'publish'],
            ]);

        return Inertia::render('Pages/Builder', [
            'page' => $pageBuilder->compile(),
            'pageData' => $page,
            'sections' => $this->getAvailableSections(),
        ]);
    }

    /**
     * Handle bulk actions
     */
    public function bulk(Request $request)
    {
        $action = $request->input('action');
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json(['error' => 'No pages selected'], 422);
        }

        $pages = ShopperPage::whereIn('id', $ids);

        return match ($action) {
            'publish' => $this->bulkPublish($pages),
            'draft' => $this->bulkDraft($pages),
            'private' => $this->bulkPrivate($pages),
            'delete' => $this->bulkDelete($pages),
            'duplicate' => $this->bulkDuplicate($pages),
            default => response()->json(['error' => 'Unknown action'], 422),
        };
    }

    /**
     * Get available templates
     */
    protected function getAvailableTemplates(): array
    {
        // This would fetch from database in real implementation
        return [
            ['id' => 1, 'name' => 'Default Template'],
            ['id' => 2, 'name' => 'Landing Page'],
            ['id' => 3, 'name' => 'Article Template'],
        ];
    }

    /**
     * Get available sections for page builder
     */
    protected function getAvailableSections(): array
    {
        // This would fetch from database in real implementation
        return [
            [
                'id' => 'hero',
                'name' => 'Hero Section',
                'description' => 'Hero banner with image and text',
                'icon' => 'layout',
            ],
            [
                'id' => 'text',
                'name' => 'Text Block',
                'description' => 'Rich text content block',
                'icon' => 'text',
            ],
            [
                'id' => 'image',
                'name' => 'Image Block',
                'description' => 'Single image with caption',
                'icon' => 'image',
            ],
        ];
    }

    /**
     * Bulk publish pages
     */
    protected function bulkPublish($pages)
    {
        $count = $pages->update(['status' => 'published']);

        return response()->json(['message' => "Published {$count} pages"]);
    }

    /**
     * Bulk set pages as draft
     */
    protected function bulkDraft($pages)
    {
        $count = $pages->update(['status' => 'draft']);

        return response()->json(['message' => "Set {$count} pages as draft"]);
    }

    /**
     * Bulk set pages as private
     */
    protected function bulkPrivate($pages)
    {
        $count = $pages->update(['status' => 'private']);

        return response()->json(['message' => "Set {$count} pages as private"]);
    }

    /**
     * Bulk delete pages
     */
    protected function bulkDelete($pages)
    {
        $count = $pages->count();
        $pages->delete();

        return response()->json(['message' => "Deleted {$count} pages"]);
    }

    /**
     * Bulk duplicate pages
     */
    protected function bulkDuplicate($pages)
    {
        $count = 0;
        $pages
            ->get()
            ->each(function ($page) use (&$count) {
                $pageDto = PageDto::from($page->toArray());
                $pageDto->title = $pageDto->title.' (Copy)';
                $pageDto->handle = '';
                $pageDto->status = 'draft';

                ShopperPage::create($pageDto->toArray());
                $count++;
            });

        return response()->json(['message' => "Duplicated {$count} pages"]);
    }
}
