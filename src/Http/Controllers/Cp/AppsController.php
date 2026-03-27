<?php

namespace Cartino\Http\Controllers\Cp;

use Cartino\Cp\Page;
use Cartino\Http\Controllers\Controller;
use Cartino\Models\App;
use Cartino\Models\AppReview;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AppsController extends Controller
{
    /**
     * Display app store / installed apps
     */
    public function index(Request $request)
    {
        $view = $request->get('view', 'store'); // 'store' or 'installed'

        if ($view === 'installed') {
            return $this->installedApps($request);
        }

        return $this->appStore($request);
    }

    /**
     * App Store view
     */
    private function appStore(Request $request)
    {
        $query = App::query()->approved();

        // Filters
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('pricing')) {
            if ($request->pricing === 'free') {
                $query->free();
            } elseif ($request->pricing === 'paid') {
                $query->paid();
            }
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")->orWhere(
                    'description',
                    'like',
                    "%{$request->search}%",
                )->orWhereJsonContains('tags', $request->search);
            });
        }

        // Sorting
        $sort = $request->get('sort', 'popular');
        switch ($sort) {
            case 'popular':
                $query->orderByDesc('install_count');
                break;
            case 'rating':
                $query->orderByDesc('rating');
                break;
            case 'newest':
                $query->orderByDesc('created_at');
                break;
            case 'name':
                $query->orderBy('name');
                break;
            case 'price':
                $query->orderBy('price');
                break;
        }

        $apps = $query->paginate(20);

        // Get categories and stats
        $categories = App::approved()
            ->select('categories')
            ->whereNotNull('categories')
            ->get()
            ->pluck('categories')
            ->flatten()
            ->unique()
            ->values();

        $stats = [
            'total_apps' => App::approved()->count(),
            'free_apps' => App::approved()->free()->count(),
            'paid_apps' => App::approved()->paid()->count(),
            'installed_apps' => App::installed()->count(),
        ];

        $page = Page::make(__('apps.store.title'))
            ->breadcrumb(__('admin.navigation.home'), '/cp')
            ->breadcrumb(__('apps.title'))
            ->secondaryActions([
                ['label' => __('apps.installed.title'), 'url' => '/cp/apps?view=installed'],
                ['label' => __('apps.store.submit'), 'url' => '/cp/apps/submit'],
            ]);

        return Inertia::render('Apps/Store', [
            'page' => $page->compile(),
            'apps' => $apps,
            'categories' => $categories,
            'stats' => $stats,
            'filters' => $request->only(['category', 'pricing', 'search', 'sort']),
        ]);
    }

    /**
     * Installed apps view
     */
    private function installedApps(Request $request)
    {
        $query = App::installed()->with('installation');

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $apps = $query->orderBy('name')->paginate(20);

        $page = Page::make(__('apps.installed.title'))
            ->breadcrumb(__('admin.navigation.home'), '/cp')
            ->breadcrumb(__('apps.title'))
            ->secondaryActions([
                ['label' => __('apps.store.browse'), 'url' => '/cp/apps'],
                ['label' => __('apps.store.submit'), 'url' => '/cp/apps/submit'],
            ]);

        return Inertia::render('Apps/Installed', [
            'page' => $page->compile(),
            'apps' => $apps,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    /**
     * Show app details
     */
    public function show(App $app)
    {
        $app->load(['reviews' => function ($query) {
            $query->approved()->latest()->take(5);
        }]);

        // Get related apps (same category)
        $relatedApps = App::approved()
            ->where('id', '!=', $app->id)
            ->where(function ($query) use ($app) {
                foreach ($app->categories ?? [] as $category) {
                    $query->orWhereJsonContains('categories', $category);
                }
            })
            ->orderByDesc('rating')
            ->take(6)
            ->get();

        $page = Page::make($app->name)
            ->breadcrumb(__('admin.navigation.home'), '/cp')
            ->breadcrumb(__('apps.title'), '/cp/apps')
            ->breadcrumb($app->name);

        // Add install/uninstall actions
        if ($app->is_installed) {
            $page->primaryAction(__('apps.actions.configure'), "/cp/apps/{$app->id}/configure")->secondaryActions([
                [
                    'label' => $app->is_active ? __('apps.actions.deactivate') : __('apps.actions.activate'),
                    'action' => $app->is_active ? 'deactivate' : 'activate',
                ],
                ['label' => __('apps.actions.uninstall'), 'action' => 'uninstall', 'destructive' => true],
            ]);
        } else {
            $page->primaryAction(__('apps.actions.install'), null, ['action' => 'install']);
        }

        return Inertia::render('Apps/Show', [
            'page' => $page->compile(),
            'app' => $app,
            'relatedApps' => $relatedApps,
            'canInstall' => $app->is_compatible && ! $app->is_installed,
        ]);
    }

    /**
     * Install an app
     */
    public function install(Request $request, App $app)
    {
        try {
            if ($app->is_installed) {
                return response()->json(['error' => __('apps.messages.already_installed')], 422);
            }

            if (! $app->is_compatible) {
                return response()->json(['error' => __('apps.messages.not_compatible')], 422);
            }

            $config = $request->input('configuration', []);
            $installation = $app->install($config);

            return response()->json([
                'message' => __('apps.messages.installed', ['name' => $app->name]),
                'installation' => $installation,
                'redirect' => "/cp/apps/{$app->id}/configure",
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Uninstall an app
     */
    public function uninstall(App $app)
    {
        try {
            $app->uninstall();

            return response()->json([
                'message' => __('apps.messages.uninstalled', ['name' => $app->name]),
                'redirect' => '/cp/apps?view=installed',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Activate an app
     */
    public function activate(App $app)
    {
        try {
            $app->activate();

            return response()->json([
                'message' => __('apps.messages.activated', ['name' => $app->name]),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Deactivate an app
     */
    public function deactivate(App $app)
    {
        try {
            $app->deactivate();

            return response()->json([
                'message' => __('apps.messages.deactivated', ['name' => $app->name]),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Configure app settings
     */
    public function configure(App $app)
    {
        if (! $app->is_installed) {
            return redirect()->route('cp.apps.show', $app);
        }

        $installation = $app->installation;

        $page = Page::make(__('apps.configure.title', ['name' => $app->name]))
            ->breadcrumb(__('admin.navigation.home'), '/cp')
            ->breadcrumb(__('apps.title'), '/cp/apps')
            ->breadcrumb($app->name, "/cp/apps/{$app->id}")
            ->breadcrumb(__('admin.actions.configure'))
            ->primaryAction(__('admin.actions.save'), null, ['form' => 'app-settings-form']);

        return Inertia::render('Apps/Configure', [
            'page' => $page->compile(),
            'app' => $app,
            'installation' => $installation,
            'settings' => $installation->settings ?? [],
        ]);
    }

    /**
     * Update app settings
     */
    public function updateSettings(Request $request, App $app)
    {
        if (! $app->is_installed) {
            return response()->json(['error' => __('apps.messages.not_installed')], 422);
        }

        $settings = $request->input('settings', []);
        $app->installation->updateSettings($settings);

        return response()->json([
            'message' => __('apps.messages.settings_updated'),
            'settings' => $app->installation->fresh()->settings,
        ]);
    }

    /**
     * Show app reviews
     */
    public function reviews(App $app)
    {
        $reviews = $app
            ->reviews()
            ->approved()
            ->with('user')
            ->orderBy('is_featured', 'desc')
            ->orderByDesc('helpful_count')
            ->orderByDesc('created_at')
            ->paginate(20);

        $stats = [
            'average_rating' => $app->rating,
            'total_reviews' => $app->review_count,
            'rating_distribution' => $app
                ->reviews()
                ->approved()
                ->selectRaw('rating, COUNT(*) as count')
                ->groupBy('rating')
                ->orderBy('rating', 'desc')
                ->get()
                ->pluck('count', 'rating')
                ->toArray(),
        ];

        $page = Page::make(__('apps.reviews.title'))
            ->breadcrumb(__('admin.navigation.home'), '/cp')
            ->breadcrumb(__('apps.title'), '/cp/apps')
            ->breadcrumb($app->name, "/cp/apps/{$app->id}")
            ->breadcrumb(__('apps.reviews.title'));

        return Inertia::render('Apps/Reviews', [
            'page' => $page->compile(),
            'app' => $app,
            'reviews' => $reviews,
            'stats' => $stats,
        ]);
    }

    /**
     * Submit a review
     */
    public function submitReview(Request $request, App $app)
    {
        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'title' => 'nullable|string|max:255',
            'review' => 'nullable|string|max:2000',
        ]);

        $review = AppReview::create([
            'app_id' => $app->id,
            'user_id' => auth()->id(),
            'rating' => $request->rating,
            'title' => $request->title,
            'review' => $request->review,
            'version_reviewed' => $app->version,
            'is_verified_purchase' => $app->is_installed,
        ]);

        return response()->json([
            'message' => __('apps.messages.review_submitted'),
            'review' => $review,
        ]);
    }

    /**
     * Get app usage analytics
     */
    public function analytics(App $app)
    {
        if (! $app->is_installed) {
            return response()->json(['error' => __('apps.messages.not_installed')], 422);
        }

        $installation = $app->installation;

        $analytics = [
            'usage_count' => $installation->usage_count,
            'last_used' => $installation->last_used_at,
            'error_count' => $installation->error_count,
            'last_error' => $installation->last_error_at,
            'uptime' => $installation->error_count === 0
                ? 100
                : max(0, 100 - (($installation->error_count / max(1, $installation->usage_count)) * 100)),
        ];

        return response()->json($analytics);
    }
}
