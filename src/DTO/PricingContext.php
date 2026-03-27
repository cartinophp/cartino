<?php

declare(strict_types=1);

namespace Cartino\DTO;

use Cartino\Models\Catalog;
use Cartino\Models\Channel;
use Cartino\Models\Customer;
use Cartino\Models\CustomerGroup;
use Cartino\Models\Market;
use Cartino\Models\Site;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Cache;
use JsonSerializable;

class PricingContext implements Arrayable, JsonSerializable
{
    public function __construct(
        public ?Market $market = null,
        public ?Site $site = null,
        public ?Channel $channel = null,
        public ?Catalog $catalog = null,
        public ?Customer $customer = null,
        public ?CustomerGroup $customerGroup = null,
        public ?string $currency = null,
        public ?string $locale = null,
        public int $quantity = 1,
        public ?string $countryCode = null,
        public ?array $metadata = null,
    ) {
        $this->resolveDefaults();
        $this->validate();
    }

    /**
     * Create from request/session context.
     */
    public static function fromRequest(): self
    {
        $marketId = session('market_id') ?? request('market_id');
        $siteId = session('site_id') ?? request('site_id');
        $channelId = session('channel_id') ?? request('channel_id');
        $catalogId = session('catalog_id') ?? request('catalog_id');
        $currency = session('currency') ?? request('currency');
        $locale = session('locale') ?? request('locale') ?? app()->getLocale();
        $countryCode = session('country_code') ?? request('country_code');

        return new self(
            market: $marketId ? Market::find($marketId) : null,
            site: $siteId ? Site::find($siteId) : null,
            channel: $channelId ? Channel::find($channelId) : null,
            catalog: $catalogId ? Catalog::find($catalogId) : null,
            customer: auth('customer')->user(),
            customerGroup: auth('customer')->user()?->customerGroup,
            currency: $currency,
            locale: $locale,
            quantity: (int) (request('quantity') ?? 1),
            countryCode: $countryCode,
        );
    }

    /**
     * Create from specific IDs.
     */
    public static function create(
        ?int $marketId = null,
        ?int $siteId = null,
        ?int $channelId = null,
        ?int $catalogId = null,
        ?int $customerId = null,
        ?int $customerGroupId = null,
        ?string $currency = null,
        ?string $locale = null,
        int $quantity = 1,
        ?string $countryCode = null,
        ?array $metadata = null,
    ): self {
        return new self(
            market: $marketId ? Market::find($marketId) : null,
            site: $siteId ? Site::find($siteId) : null,
            channel: $channelId ? Channel::find($channelId) : null,
            catalog: $catalogId ? Catalog::find($catalogId) : null,
            customer: $customerId ? Customer::find($customerId) : null,
            customerGroup: $customerGroupId ? CustomerGroup::find($customerGroupId) : null,
            currency: $currency,
            locale: $locale,
            quantity: $quantity,
            countryCode: $countryCode,
            metadata: $metadata,
        );
    }

    /**
     * Resolve defaults from hierarchy: Market → Site → Channel → Config.
     */
    protected function resolveDefaults(): void
    {
        // Currency resolution: explicit > customer > market > site > channel > config
        if (! $this->currency) {
            $this->currency =
                $this->customer?->preferred_currency ??
                $this->market?->default_currency ??
                    $this->site?->default_currency ??
                        $this->channel?->getDefaultCurrency() ?? config('cartino.currency', 'EUR');
        }

        // Locale resolution: explicit > customer > market > site > channel > app
        if (! $this->locale) {
            $this->locale =
                $this->customer?->locale ??
                $this->market?->default_locale ??
                    $this->site?->locale ?? $this->channel?->getDefaultLocale() ?? app()->getLocale();
        }

        // Catalog resolution: explicit > market > site default > customer group default
        if (! $this->catalog) {
            $this->catalog = $this->market?->catalog ?? $this->site?->defaultCatalog()->first() ?? $this->customerGroup
                ?->catalogs()
                ->wherePivot('is_default', true)
                ->first();
        }

        // Country code resolution: explicit > customer > market > site
        if (! $this->countryCode) {
            $this->countryCode = $this->customer
                ?->addresses()
                ->where('is_default', true)
                ->first()
                ?->country_code ?? (! empty($this->market?->countries) ? $this->market->countries[0] : null) ??
                (! empty($this->site?->countries) ? $this->site->countries[0] : null);
        }

        // Customer group resolution
        if (! $this->customerGroup && $this->customer) {
            $this->customerGroup = $this->customer->customerGroup;
        }
    }

    /**
     * Validate the context.
     */
    protected function validate(): void
    {
        if ($this->quantity < 1) {
            throw new \InvalidArgumentException('Quantity must be at least 1');
        }

        if ($this->currency && strlen($this->currency) !== 3) {
            throw new \InvalidArgumentException('Currency must be a 3-letter ISO code');
        }

        if ($this->countryCode && strlen($this->countryCode) !== 2) {
            throw new \InvalidArgumentException('Country code must be a 2-letter ISO code');
        }
    }

    /**
     * Check if context supports a specific currency.
     */
    public function supportsCurrency(string $currency): bool
    {
        if ($this->market && ! $this->market->supportsCurrency($currency)) {
            return false;
        }

        if ($this->site && ! in_array($currency, $this->site->supported_currencies)) {
            return false;
        }

        if ($this->channel && ! $this->channel->supportsCurrency($currency)) {
            return false;
        }

        return true;
    }

    /**
     * Check if context supports a specific locale.
     */
    public function supportsLocale(string $locale): bool
    {
        if ($this->market && ! $this->market->supportsLocale($locale)) {
            return false;
        }

        if ($this->site && ! in_array($locale, $this->site->supported_locales)) {
            return false;
        }

        if ($this->channel && ! $this->channel->supportsLocale($locale)) {
            return false;
        }

        return true;
    }

    /**
     * Get cache key for this context.
     */
    public function getCacheKey(string $prefix = 'price'): string
    {
        return sprintf(
            '%s:m%s:s%s:ch%s:ca%s:cg%s:cur%s:qty%d',
            $prefix,
            $this->market?->id ?? 'null',
            $this->site?->id ?? 'null',
            $this->channel?->id ?? 'null',
            $this->catalog?->id ?? 'null',
            $this->customerGroup?->id ?? 'null',
            $this->currency ?? 'null',
            $this->quantity,
        );
    }

    /**
     * Get tax-inclusive pricing preference.
     */
    public function isTaxInclusive(): bool
    {
        return
            $this->market?->tax_included_in_prices ??
            $this->site?->tax_included_in_prices ?? config('cartino.tax_included_in_prices', false);
    }

    /**
     * Get tax region for this context.
     */
    public function getTaxRegion(): ?string
    {
        return $this->market?->tax_region ?? $this->site?->tax_region ?? $this->countryCode;
    }

    /**
     * Clone with different values.
     */
    public function with(array $attributes): self
    {
        return new self(
            market: $attributes['market'] ?? $this->market,
            site: $attributes['site'] ?? $this->site,
            channel: $attributes['channel'] ?? $this->channel,
            catalog: $attributes['catalog'] ?? $this->catalog,
            customer: $attributes['customer'] ?? $this->customer,
            customerGroup: $attributes['customerGroup'] ?? $this->customerGroup,
            currency: $attributes['currency'] ?? $this->currency,
            locale: $attributes['locale'] ?? $this->locale,
            quantity: $attributes['quantity'] ?? $this->quantity,
            countryCode: $attributes['countryCode'] ?? $this->countryCode,
            metadata: $attributes['metadata'] ?? $this->metadata,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'market_id' => $this->market?->id,
            'market_code' => $this->market?->code,
            'site_id' => $this->site?->id,
            'site_handle' => $this->site?->handle,
            'channel_id' => $this->channel?->id,
            'channel_slug' => $this->channel?->slug,
            'catalog_id' => $this->catalog?->id,
            'catalog_slug' => $this->catalog?->slug,
            'customer_id' => $this->customer?->id,
            'customer_group_id' => $this->customerGroup?->id,
            'currency' => $this->currency,
            'locale' => $this->locale,
            'quantity' => $this->quantity,
            'country_code' => $this->countryCode,
            'tax_inclusive' => $this->isTaxInclusive(),
            'tax_region' => $this->getTaxRegion(),
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Convert to JSON.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert to string for debugging.
     */
    public function __toString(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }

    /**
     * Store context in session.
     */
    public function saveToSession(): void
    {
        session([
            'market_id' => $this->market?->id,
            'site_id' => $this->site?->id,
            'channel_id' => $this->channel?->id,
            'catalog_id' => $this->catalog?->id,
            'currency' => $this->currency,
            'locale' => $this->locale,
            'country_code' => $this->countryCode,
        ]);
    }

    /**
     * Store context in cache.
     */
    public function cache(string $key, int $ttl = 3600): bool
    {
        return Cache::put($key, $this->toArray(), $ttl);
    }

    /**
     * Load context from cache.
     */
    public static function fromCache(string $key): ?self
    {
        $data = Cache::get($key);

        if (! $data) {
            return null;
        }

        return self::create(
            marketId: $data['market_id'] ?? null,
            siteId: $data['site_id'] ?? null,
            channelId: $data['channel_id'] ?? null,
            catalogId: $data['catalog_id'] ?? null,
            customerId: $data['customer_id'] ?? null,
            customerGroupId: $data['customer_group_id'] ?? null,
            currency: $data['currency'] ?? null,
            locale: $data['locale'] ?? null,
            quantity: $data['quantity'] ?? 1,
            countryCode: $data['country_code'] ?? null,
            metadata: $data['metadata'] ?? null,
        );
    }
}
