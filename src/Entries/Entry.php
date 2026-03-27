<?php

namespace Cartino\Entries;

use ArrayAccess;
use Carbon\Carbon;
use Cartino\Contracts\Entries\Entry as Contract;
use Cartino\Contracts\Entries\EntryRepository;
use Cartino\Contracts\Routing\UrlBuilder;
use Cartino\Events\Entries\EntryCreated;
use Cartino\Events\Entries\EntryCreating;
use Cartino\Events\Entries\EntryDeleted;
use Cartino\Events\Entries\EntryDeleting;
use Cartino\Events\Entries\EntrySaved;
use Cartino\Events\Entries\EntrySaving;
use Cartino\Facades\Category;
use Cartino\Facades\Site;
use Cartino\Support\Traits\FluentlyGetsAndSets;
use Cartino\Traits\ContainsCascadingData;
use Cartino\Traits\ExistsAsFile;
use Cartino\Traits\HasAugmentedData;
use Illuminate\Contracts\Support\Arrayable;

class Entry implements Arrayable, ArrayAccess, Contract
{
    use ContainsCascadingData, ExistsAsFile, FluentlyGetsAndSets, HasAugmentedData;

    protected $id;

    protected $slug;

    protected $uri;

    protected $collection;

    protected $blueprint;

    protected $locale;

    protected $origin;

    protected $published = true;

    protected $date;

    protected $data = [];

    protected $supplements = [];

    protected $withEvents = true;

    protected $afterSaveCallbacks = [];

    public function __construct()
    {
        $this->data = collect();
        $this->supplements = collect();
    }

    public function id($id = null)
    {
        return $this->fluentlyGetOrSet('id')->args(func_get_args());
    }

    public function slug($slug = null)
    {
        return $this->fluentlyGetOrSet('slug')->args(func_get_args());
    }

    public function uri($uri = null)
    {
        if (func_num_args() === 0) {
            return $this->uri ?? $this->buildUri();
        }

        $this->uri = $uri;

        return $this;
    }

    protected function buildUri()
    {
        if (! $this->route()) {
            return null;
        }

        return app(UrlBuilder::class)
            ->content($this)
            ->merge($this->routeData())
            ->build($this->route());
    }

    public function collection($collection = null)
    {
        if (func_num_args() === 0) {
            return ($this->collection instanceof \Cartino\Collections\Category)
                ? $this->collection
                : Category::findByHandle($this->collection);
        }

        $this->collection = $collection;

        return $this;
    }

    public function collectionHandle()
    {
        return ($this->collection instanceof \Cartino\Collections\Category)
            ? $this->collection->handle()
            : $this->collection;
    }

    public function blueprint($blueprint = null)
    {
        if (func_num_args() === 0) {
            return $this->blueprint ?? $this->collection()->entryBlueprints()->first();
        }

        $this->blueprint = $blueprint;

        return $this;
    }

    public function locale($locale = null)
    {
        return $this->fluentlyGetOrSet('locale')
            ->getter(function ($locale) {
                return $locale ?? Site::default()->handle();
            })
            ->args(func_get_args());
    }

    public function site()
    {
        return Site::get($this->locale());
    }

    public function published($published = null)
    {
        return $this->fluentlyGetOrSet('published')->args(func_get_args());
    }

    public function private($private = null)
    {
        return $this->fluentlyGetOrSet('private')->args(func_get_args());
    }

    public function date($date = null)
    {
        return $this->fluentlyGetOrSet('date')
            ->setter(function ($date) {
                return $this->asCarbon($date);
            })
            ->args(func_get_args());
    }

    public function hasDate()
    {
        return $this->date && $this->collection()->dated();
    }

    protected function asCarbon($value)
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if (is_null($value)) {
            return null;
        }

        return Carbon::parse($value);
    }

    public function data($data = null)
    {
        if (func_num_args() === 0) {
            return $this->data;
        }

        $this->data = collect($data);

        return $this;
    }

    public function merge($data)
    {
        $this->data = $this->data->merge($data);

        return $this;
    }

    public function get($key, $default = null)
    {
        return $this->data->get($key, $default);
    }

    public function set($key, $value)
    {
        $this->data->put($key, $value);

        return $this;
    }

    public function remove($key)
    {
        $this->data->forget($key);

        return $this;
    }

    public function has($key)
    {
        return $this->data->has($key);
    }

    public function values()
    {
        return $this->data();
    }

    public function title()
    {
        return $this->get('title', $this->slug());
    }

    public function editUrl()
    {
        return $this->cpUrl('collections.entries.edit');
    }

    public function updateUrl()
    {
        return $this->cpUrl('collections.entries.update');
    }

    public function publishUrl()
    {
        return $this->cpUrl('collections.entries.published.store');
    }

    public function unpublishUrl()
    {
        return $this->cpUrl('collections.entries.published.destroy');
    }

    protected function cpUrl($route)
    {
        if (! ($id = $this->id())) {
            return null;
        }

        return cp_route($route, [$this->collectionHandle(), $id]);
    }

    public function route()
    {
        return $this->collection()->route($this->locale());
    }

    public function routeData()
    {
        $data = $this->values()->merge([
            'id' => $this->id(),
            'slug' => $this->slug(),
            'published' => $this->published(),
        ]);

        if ($this->hasDate()) {
            $data = $data->merge([
                'date' => $this->date(),
                'year' => $this->date()->format('Y'),
                'month' => $this->date()->format('m'),
                'day' => $this->date()->format('d'),
            ]);
        }

        return $data->all();
    }

    public function absoluteUrl()
    {
        if (! ($uri = $this->uri())) {
            return null;
        }

        return vsprintf('%s/%s', [
            rtrim($this->site()->absoluteUrl(), '/'),
            ltrim($uri, '/'),
        ]);
    }

    public function url()
    {
        if (! ($uri = $this->uri())) {
            return null;
        }

        return vsprintf('%s/%s', [
            rtrim($this->site()->url(), '/'),
            ltrim($uri, '/'),
        ]);
    }

    // E-commerce specific methods
    public function isProduct()
    {
        return $this->collection()->isProductCollection();
    }

    public function isCustomer()
    {
        return $this->collection()->isCustomerCollection();
    }

    public function isOrder()
    {
        return $this->collection()->isOrderCollection();
    }

    public function isCategory()
    {
        return $this->collection()->isCategoryCollection();
    }

    public function price()
    {
        if (! $this->isProduct()) {
            return null;
        }

        return $this->get($this->collection()->priceField());
    }

    public function sku()
    {
        if (! $this->isProduct()) {
            return null;
        }

        return $this->get($this->collection()->skuField());
    }

    public function inventory()
    {
        if (! $this->isProduct()) {
            return null;
        }

        return $this->get($this->collection()->inventoryField());
    }

    public function formattedPrice($currency = 'USD')
    {
        $price = $this->price();

        if (is_null($price)) {
            return null;
        }

        return number_format($price, 2, '.', ',').' '.$currency;
    }

    public function inStock()
    {
        $inventory = $this->inventory();

        return $inventory === null || $inventory > 0;
    }

    public function save()
    {
        $isNew = is_null($this->id());

        $withEvents = $this->withEvents;
        $this->withEvents = true;

        $afterSaveCallbacks = $this->afterSaveCallbacks;
        $this->afterSaveCallbacks = [];

        if ($withEvents) {
            if ($isNew) {
                EntryCreating::dispatch($this);
            }

            EntrySaving::dispatch($this);
        }

        $this->ensureId();

        // Save the entry through the repository
        app(EntryRepository::class)->save($this);

        if ($withEvents) {
            if ($isNew) {
                EntryCreated::dispatch($this);
            }

            EntrySaved::dispatch($this);
        }

        foreach ($afterSaveCallbacks as $callback) {
            $callback($this);
        }

        return $this;
    }

    public function delete()
    {
        EntryDeleting::dispatch($this);

        app(EntryRepository::class)->delete($this);

        EntryDeleted::dispatch($this);
    }

    protected function ensureId()
    {
        if ($this->id()) {
            return $this;
        }

        $this->id(app('stache')->generateId());

        return $this;
    }

    public function path()
    {
        return vsprintf('%s/%s/%s.md', [
            rtrim(app('stache')->store('entries')->directory(), '/'),
            $this->collectionHandle(),
            $this->slug(),
        ]);
    }

    public function toArray()
    {
        $data = $this->data->all();

        if ($this->hasDate()) {
            $data['date'] = $this->date()->toDateTimeString();
        }

        return array_merge([
            'id' => $this->id(),
            'slug' => $this->slug(),
            'uri' => $this->uri(),
            'published' => $this->published(),
            'locale' => $this->locale(),
            'collection' => $this->collectionHandle(),
        ], $data);
    }

    // ArrayAccess implementation
    public function offsetExists($key): bool
    {
        return $this->has($key);
    }

    public function offsetGet($key): mixed
    {
        return $this->get($key);
    }

    public function offsetSet($key, $value): void
    {
        $this->set($key, $value);
    }

    public function offsetUnset($key): void
    {
        $this->remove($key);
    }
}
