<?php

namespace Cartino\Collections;

use ArrayAccess;
use Cartino\Contracts\Collections\Category as Contract;
use Cartino\Events\Collections\CollectionCreated;
use Cartino\Events\Collections\CollectionCreating;
use Cartino\Events\Collections\CollectionDeleted;
use Cartino\Events\Collections\CollectionDeleting;
use Cartino\Events\Collections\CollectionSaved;
use Cartino\Events\Collections\CollectionSaving;
use Cartino\Facades\Blink;
use Cartino\Facades\Blueprint;
use Cartino\Facades\Entry;
use Cartino\Facades\Site;
use Cartino\Facades\Stache;
use Cartino\Support\Arr;
use Cartino\Support\Traits\FluentlyGetsAndSets;
use Cartino\Traits\ContainsCascadingData;
use Cartino\Traits\ExistsAsFile;
use Cartino\Traits\HasAugmentedData;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\Contracts\Data\Augmentable as AugmentableContract;

use function Cartino\trans as __;

class Category implements Arrayable, ArrayAccess, AugmentableContract, Contract
{
    use ContainsCascadingData, ExistsAsFile, FluentlyGetsAndSets, HasAugmentedData;

    protected $handle;

    protected $routes = [];

    private $cachedRoutes = null;

    protected $mount;

    protected $title;

    protected $template;

    protected $layout;

    protected $sites;

    protected $propagate = false;

    protected $blueprints = [];

    protected $searchIndex;

    protected $dated = false;

    protected $sortField;

    protected $sortDirection;

    protected $revisions = false;

    protected $positions;

    protected $defaultPublishState = true;

    protected $originBehavior = 'select';

    protected $futureDateBehavior = 'public';

    protected $pastDateBehavior = 'public';

    protected $structure;

    protected $structureContents;

    protected $taxonomies = [];

    protected $requiresSlugs = true;

    protected $titleFormats = [];

    protected $previewTargets = [];

    protected $autosave;

    protected $entryBlueprints;

    protected $withEvents = true;

    // E-commerce specific properties
    protected $productCollection = false;

    protected $customerCollection = false;

    protected $orderCollection = false;

    protected $categoryCollection = false;

    protected $ecommerceType = null;

    protected $priceField = 'price';

    protected $skuField = 'sku';

    protected $inventoryField = 'inventory';

    public function __construct()
    {
        $this->cascade = collect();
    }

    public function id()
    {
        return $this->handle();
    }

    public function handle($handle = null)
    {
        if (func_num_args() === 0) {
            return $this->handle;
        }

        $this->handle = $handle;

        return $this;
    }

    public function routes($routes = null)
    {
        return $this->fluentlyGetOrSet('routes')
            ->getter(function ($routes) {
                if ($this->cachedRoutes !== null) {
                    return $this->cachedRoutes;
                }

                return $this->cachedRoutes = $this->sites()->mapWithKeys(function ($site) use ($routes) {
                    $siteRoute = is_string($routes) ? $routes : ($routes[$site] ?? null);

                    return [$site => $siteRoute];
                });
            })
            ->afterSetter(fn () => $this->cachedRoutes = null)
            ->args(func_get_args());
    }

    public function route($site)
    {
        return $this->routes()->get($site);
    }

    // E-commerce specific methods
    public function isProductCollection($value = null)
    {
        return $this->fluentlyGetOrSet('productCollection')->args(func_get_args());
    }

    public function isCustomerCollection($value = null)
    {
        return $this->fluentlyGetOrSet('customerCollection')->args(func_get_args());
    }

    public function isOrderCollection($value = null)
    {
        return $this->fluentlyGetOrSet('orderCollection')->args(func_get_args());
    }

    public function isCategoryCollection($value = null)
    {
        return $this->fluentlyGetOrSet('categoryCollection')->args(func_get_args());
    }

    public function ecommerceType($type = null)
    {
        return $this->fluentlyGetOrSet('ecommerceType')->args(func_get_args());
    }

    public function priceField($field = null)
    {
        return $this->fluentlyGetOrSet('priceField')->args(func_get_args());
    }

    public function skuField($field = null)
    {
        return $this->fluentlyGetOrSet('skuField')->args(func_get_args());
    }

    public function inventoryField($field = null)
    {
        return $this->fluentlyGetOrSet('inventoryField')->args(func_get_args());
    }

    public function title($title = null)
    {
        if (func_num_args() === 0) {
            return $this->title ?: ucfirst($this->handle);
        }

        $this->title = $title;

        return $this;
    }

    public function sites($sites = null)
    {
        return $this->fluentlyGetOrSet('sites')
            ->getter(function ($sites) {
                return collect(Site::multiEnabled() ? $sites : [Site::default()->handle()]);
            })
            ->args(func_get_args());
    }

    public function queryEntries()
    {
        return Entry::query()->where('collection', $this->handle());
    }

    public function hasVisibleEntryBlueprint()
    {
        return $this->entryBlueprints()->reject->hidden()->isNotEmpty();
    }

    public function entryBlueprints()
    {
        $blink = 'collection-entry-blueprints-'.$this->handle();

        return Blink::once($blink, function () {
            return $this->getEntryBlueprints();
        });
    }

    private function getEntryBlueprints()
    {
        $blueprints = Blueprint::in('collections/'.$this->handle());

        if ($blueprints->isEmpty()) {
            $blueprints = collect([$this->fallbackEntryBlueprint()]);
        }

        return $blueprints;
    }

    protected function fallbackEntryBlueprint()
    {
        return Blueprint::makeFromFields([
            'title' => ['type' => 'text', 'required' => true],
            'content' => ['type' => 'markdown'],
        ])->setHandle('default');
    }

    public function createLabel()
    {
        return __('Create :collection Entry', ['collection' => $this->title()]);
    }

    public function editUrl()
    {
        return cp_route('collections.edit', $this->handle());
    }

    public function deleteUrl()
    {
        return cp_route('collections.destroy', $this->handle());
    }

    public function createEntryUrl($site = null)
    {
        $site = $site ?? $this->sites()->first();

        return cp_route('collections.entries.create', [$this->handle(), $site]);
    }

    public function showUrl()
    {
        return cp_route('collections.show', $this->handle());
    }

    public function save()
    {
        $isNew = is_null(Facades\Category::find($this->id()));

        $withEvents = $this->withEvents;
        $this->withEvents = true;

        $afterSaveCallbacks = $this->afterSaveCallbacks;
        $this->afterSaveCallbacks = [];

        if ($withEvents) {
            if ($isNew) {
                CollectionCreating::dispatch($this);
            }

            CollectionSaving::dispatch($this);
        }

        Facades\Category::save($this);

        Blink::forget('collection-handles');
        Blink::forget('mounted-collections');
        Blink::flushStartingWith("collection-{$this->id()}");

        if ($withEvents) {
            if ($isNew) {
                CollectionCreated::dispatch($this);
            }

            CollectionSaved::dispatch($this);
        }

        foreach ($afterSaveCallbacks as $callback) {
            $callback($this);
        }

        return $this;
    }

    public function delete()
    {
        CollectionDeleting::dispatch($this);

        Facades\Category::delete($this);

        CollectionDeleted::dispatch($this);
    }

    public function path()
    {
        return vsprintf('%s/%s.yaml', [
            rtrim(Stache::store('collections')->directory(), '/'),
            $this->handle,
        ]);
    }

    public function toArray()
    {
        $array = [
            'title' => $this->title,
            'template' => $this->template,
            'layout' => $this->layout,
            'dated' => $this->dated,
            'sort_dir' => $this->sortDirection,
            'sort_field' => $this->sortField,
            'taxonomies' => $this->taxonomies,
            'default_status' => $this->defaultPublishState ? 'published' : 'draft',
            'revisions' => $this->revisions,
            'sites' => $this->sites,
            'propagate' => $this->propagate,
            'origin_behavior' => $this->originBehavior,
            'future_date_behavior' => $this->futureDateBehavior,
            'past_date_behavior' => $this->pastDateBehavior,
            'mount' => $this->mount,
            'blueprints' => $this->blueprints,
            'search_index' => $this->searchIndex,
            'structured' => $this->hasStructure(),
            'require_slugs' => $this->requiresSlugs,
            'title_format' => $this->titleFormats,
            'preview_targets' => $this->previewTargets,
            'autosave' => $this->autosave,
        ];

        // Add e-commerce specific fields
        if ($this->ecommerceType) {
            $array['ecommerce_type'] = $this->ecommerceType;
            $array['price_field'] = $this->priceField;
            $array['sku_field'] = $this->skuField;
            $array['inventory_field'] = $this->inventoryField;
        }

        $route = is_string($this->routes) ? $this->routes : $this->routes()->filter()->all();

        return Arr::removeNullValues(array_merge($array, [
            'route' => $route,
            'slugs' => $this->requiresSlugs() === true ? null : false,
        ]));
    }

    // ArrayAccess implementation
    public function offsetExists($key): bool
    {
        return array_key_exists($key, $this->toArray());
    }

    public function offsetGet($key): mixed
    {
        return $this->toArray()[$key];
    }

    public function offsetSet($key, $value): void
    {
        throw new \Exception('Category is immutable');
    }

    public function offsetUnset($key): void
    {
        throw new \Exception('Category is immutable');
    }
}
