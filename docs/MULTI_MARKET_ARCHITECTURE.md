# Multi-Market Architecture - CartinoPHP

## 📋 Overview

CartinoPHP implementa un'architettura enterprise multi-market ispirata a **Shopify Markets** e **Salesforce Commerce Cloud**, permettendo di gestire cataloghi, prezzi e tassazione per mercati geografici e segmenti business diversi.

---

## 🏗️ Architettura Principale

### Entità Core

```
Market (IT-B2C, US-Wholesale, EU-B2B)
  ├── Site (shop.it, shop.com)
  │   └── Channel (web, mobile, pos)
  ├── Catalog (Retail, B2B, VIP)
  ├── Currency (EUR, USD, GBP)
  └── Locale (it_IT, en_US, fr_FR)
```

### Gerarchia di Contesto

**Price Resolution Priority:**
```
1. Market + Site + Channel + Catalog (most specific)
2. Market + Site + Channel
3. Market + Catalog
4. Site + Catalog
5. Market only
6. Site only
7. Base price (all nulls)
```

---

## 🔑 Componenti Chiave

### 1. Market Model

Il **Market** rappresenta un mercato geografico o business:

```php
Market::create([
    'code' => 'IT-B2C',
    'name' => 'Italia B2C',
    'type' => 'b2c', // b2c, b2b, wholesale, marketplace
    'countries' => ['IT', 'SM', 'VA'],
    'default_currency' => 'EUR',
    'supported_currencies' => ['EUR', 'USD'],
    'default_locale' => 'it_IT',
    'supported_locales' => ['it_IT', 'en_US'],
    'tax_included_in_prices' => true,
    'tax_region' => 'IT',
    'catalog_id' => 1, // Optional catalog override
    'payment_methods' => ['stripe', 'paypal', 'bank_transfer'],
    'shipping_methods' => ['standard', 'express'],
]);
```

**Differenze con Site:**
- **Market** = Mercato business (IT-B2C, UK-Wholesale)
- **Site** = Dominio tecnico/frontend (shop.it, uk.shop.com)
- Un Market può avere più Sites

---

### 2. PricingContext DTO

Il **PricingContext** è un Value Object che rappresenta il contesto di pricing:

```php
use Cartino\DTO\PricingContext;

$context = PricingContext::fromRequest();
// or
$context = new PricingContext(
    market: $market,
    site: $site,
    channel: $channel,
    catalog: $catalog,
    customer: auth()->user(),
    currency: 'EUR',
    locale: 'it_IT',
    quantity: 5
);
```

**Vantaggi:**
- Type safety
- Default resolution automatica dalla gerarchia
- Serializzazione per cache
- Validazione integrata

---

### 3. Price Resolution Service

Il **PriceResolutionService** centralizza la logica di risoluzione prezzi:

```php
use Cartino\Services\PriceResolutionService;

$service = app(PriceResolutionService::class);

// Single price resolution
$price = $service->resolve($variant, $context);

// Bulk resolution (optimized)
$prices = $service->resolveBulk($variants, $context);

// Get quantity tiers
$tiers = $service->getTiers($variant, $context);
```

**Formula Prezzo:**
```
Price = Variant + Market + Site + Channel + Catalog + Currency + Quantity
```

---

### 4. Translation System

Sistema traduzione enterprise con fallback chain:

```php
use Cartino\Traits\Translatable;

class Product extends Model
{
    use Translatable;

    protected array $translatable = ['name', 'description', 'slug'];
}

// Set translation
$product->setTranslation('name', 'Prodotto Test', 'it_IT');

// Get with fallback (it_IT → it → en)
$name = $product->translate('name', 'it_IT');

// Magic getter
$nameIT = $product->name_it; // or $product->{'name:it'}
```

**Fallback Chain:**
```
it_IT → it → en_US → en
```

---

### 5. Market Configuration Service

Gestisce configurazioni specifiche per market:

```php
use Cartino\Services\MarketConfigurationService;

$config = app(MarketConfigurationService::class);

// Payment methods
$methods = $config->getAvailablePaymentMethods($market);

// Shipping methods
$shipping = $config->getAvailableShippingMethods($market, 'IT');

// Tax calculation
$tax = $config->calculateTax(10000, $market, 'IT');

// Configuration summary
$summary = $config->getConfigurationSummary($market);
```

---

### 6. Multi-Market Routing

Supporta pattern URL `{market}/{locale}/{slug}`:

```php
use Cartino\Support\MarketRouteHelper;

// Generate market-aware URL
$url = market_url('/products/tshirt', $market, 'it_IT');
// => /IT-B2C/it_IT/products/tshirt

// Generate route
$route = market_route('product.show', ['slug' => 'tshirt']);

// Switch market/locale
$switchUrl = switch_market($newMarket, 'en_US');
```

**Middleware:**
```php
// In routes/web.php
Route::middleware(['web', 'multimarket'])->group(function () {
    Route::get('/{market}/{locale?}/products/{slug}', [ProductController::class, 'show']);
});
```

---

## 💾 Database Schema

### Markets Table

```sql
CREATE TABLE markets (
    id BIGINT PRIMARY KEY,
    handle VARCHAR UNIQUE,
    code VARCHAR(10) UNIQUE, -- IT-B2C
    name VARCHAR,
    type VARCHAR, -- b2c, b2b, wholesale, marketplace
    countries JSONB, -- ["IT", "FR", "ES"]
    default_currency VARCHAR(3),
    supported_currencies JSONB,
    default_locale VARCHAR(10),
    supported_locales JSONB,
    tax_included_in_prices BOOLEAN,
    tax_region VARCHAR,
    catalog_id BIGINT,
    payment_methods JSONB,
    shipping_methods JSONB,
    status VARCHAR,
    -- ...timestamps
);
```

### Prices Table

```sql
CREATE TABLE prices (
    id BIGINT PRIMARY KEY,
    product_variant_id BIGINT,
    market_id BIGINT NULL,
    site_id BIGINT NULL,
    channel_id BIGINT NULL,
    price_list_id BIGINT NULL,
    currency VARCHAR(3),
    amount BIGINT, -- cents for precision
    compare_at_amount BIGINT NULL,
    tax_included BOOLEAN,
    tax_rate DECIMAL(8,4),
    min_quantity INT DEFAULT 1,
    max_quantity INT NULL,
    starts_at TIMESTAMP NULL,
    ends_at TIMESTAMP NULL,
    is_active BOOLEAN,

    UNIQUE(product_variant_id, market_id, site_id, channel_id, price_list_id, currency, min_quantity)
);
```

### Translations Table

```sql
CREATE TABLE translations (
    id BIGINT PRIMARY KEY,
    translatable_type VARCHAR,
    translatable_id BIGINT,
    locale VARCHAR(10),
    key VARCHAR, -- field name
    value TEXT,
    is_verified BOOLEAN,

    UNIQUE(translatable_type, translatable_id, locale, key)
);
```

---

## 🔄 Migration da Sistema Vecchio

### 1. Da VariantPrice a Price/PriceList

**Vecchio sistema (deprecato):**
```php
VariantPrice::create([
    'product_variant_id' => $variant->id,
    'site_id' => 1,
    'customer_group_id' => 2,
    'price' => 99.99, // FLOAT - impreciso!
]);
```

**Nuovo sistema:**
```php
Price::create([
    'product_variant_id' => $variant->id,
    'market_id' => 1,
    'site_id' => 1,
    'price_list_id' => 2, // instead of customer_group_id
    'currency' => 'EUR',
    'amount' => 9999, // INT in cents - preciso!
]);
```

### 2. Migrare Prezzi Esistenti

```php
// Artisan command per migrazione
php artisan cartino:migrate-prices

// O manualmente
foreach (VariantPrice::all() as $oldPrice) {
    Price::create([
        'product_variant_id' => $oldPrice->product_variant_id,
        'site_id' => $oldPrice->site_id,
        'currency' => $oldPrice->currency,
        'amount' => (int)($oldPrice->price * 100), // Convert to cents
        'is_active' => true,
    ]);
}
```

---

## 🚀 Usage Examples

### Example 1: E-commerce Multi-Market

```php
// Setup markets
$marketEU = Market::create([
    'code' => 'EU-B2C',
    'countries' => ['IT', 'FR', 'DE'],
    'default_currency' => 'EUR',
]);

$marketUS = Market::create([
    'code' => 'US-B2C',
    'countries' => ['US'],
    'default_currency' => 'USD',
]);

// Create prices per market
Price::create([
    'product_variant_id' => $variant->id,
    'market_id' => $marketEU->id,
    'currency' => 'EUR',
    'amount' => 10000, // €100.00
]);

Price::create([
    'product_variant_id' => $variant->id,
    'market_id' => $marketUS->id,
    'currency' => 'USD',
    'amount' => 11000, // $110.00
]);

// Resolve price for customer
$context = PricingContext::fromRequest();
$price = app(PriceResolutionService::class)->resolve($variant, $context);
```

### Example 2: B2B with Customer Groups

```php
// Create B2B catalog
$catalogB2B = Catalog::create([
    'title' => 'B2B Wholesale',
    'currency' => 'EUR',
]);

// Assign to customer group
$customerGroup->catalogs()->attach($catalogB2B->id, ['is_default' => true]);

// Create B2B price
Price::create([
    'product_variant_id' => $variant->id,
    'market_id' => $marketEU->id,
    'price_list_id' => $catalogB2B->id,
    'currency' => 'EUR',
    'amount' => 8000, // 20% B2B discount
]);

// Context resolves catalog from customer group automatically
$context = new PricingContext(
    market: $marketEU,
    customer: $b2bCustomer, // Has B2B customer group
    quantity: 10
);

$price = $service->resolve($variant, $context);
// Returns €80.00 B2B price
```

### Example 3: Multi-Language Product

```php
use Cartino\Traits\Translatable;

class Product extends Model
{
    use Translatable;

    protected array $translatable = ['name', 'description'];
}

$product = Product::create([
    'name' => 'T-Shirt',
    'description' => 'A comfortable t-shirt',
]);

// Add translations
$product->setTranslations([
    'it_IT' => [
        'name' => 'Maglietta',
        'description' => 'Una maglietta comoda',
    ],
    'fr_FR' => [
        'name' => 'T-Shirt',
        'description' => 'Un t-shirt confortable',
    ],
]);

// Get translated (with fallback)
app()->setLocale('it_IT');
echo $product->translate('name'); // "Maglietta"

app()->setLocale('es_ES'); // Not translated
echo $product->translate('name'); // "T-Shirt" (fallback to default)
```

---

## ⚡ Performance

### Caching Strategy

```php
// Price resolution uses cache automatically
$cacheKey = $context->getCacheKey("price:{$variant->id}");
// => "price:123:m1:s2:ch3:ca4:curEUR:qty5"

// Cache for 5 minutes
Cache::remember($cacheKey, 300, fn() => $service->resolve($variant, $context));

// Clear cache when prices change
$service->clearCache($variant);
```

### Bulk Operations

```php
// Load 100 variants with prices in 2 queries instead of 100
$variants = ProductVariant::limit(100)->get();
$prices = $service->resolveBulk($variants, $context);
```

---

## 🔒 Security

### Market Access Control

```php
// Policy
class MarketPolicy
{
    public function view(User $user, Market $market): bool
    {
        return $market->is_published
            && ($user->hasRole('admin') || $market->status === 'active');
    }
}

// Usage
$this->authorize('view', $market);
```

### Price Visibility

```php
// Hide prices for unauthorized users
if (! auth()->check() || ! auth()->user()->can('view-prices', $market)) {
    throw new UnauthorizedException('Cannot view prices for this market');
}
```

---

## 📊 Monitoring & Debugging

### Price Resolution Logging

```php
// Enable debug logging
Log::debug('Price resolved', [
    'variant_id' => $variant->id,
    'price_id' => $price->id,
    'amount' => $price->amount,
    'context' => $context->toArray(),
]);
```

### Market Configuration Validation

```php
$config = app(MarketConfigurationService::class);
$errors = $config->validate($market);

if (!empty($errors)) {
    throw new ValidationException('Market configuration invalid: ' . implode(', ', $errors));
}
```

---

## 🎯 Best Practices

1. **Always use PricingContext** invece di parametri sparsi
2. **Prezzi in centesimi (INT)** per evitare arrotondamenti
3. **Cache aggressiva** con TTL 5-15 minuti
4. **Fallback locale chain** sempre attivo
5. **Validare market configuration** prima di andare live
6. **Separate payment/shipping** per market
7. **Non convertire valute dinamicamente** per checkout (solo suggerimento)
8. **Testare price resolution** con tutti i contesti possibili

---

## 📚 API Reference

### PricingContext

```php
// Creation methods
PricingContext::fromRequest()
PricingContext::create($marketId, $siteId, ...)

// Properties
$context->market
$context->site
$context->channel
$context->catalog
$context->currency
$context->locale
$context->quantity

// Methods
$context->getCacheKey($prefix)
$context->isTaxInclusive()
$context->getTaxRegion()
$context->supportsCurrency($currency)
$context->with(['currency' => 'USD'])
$context->toArray()
```

### PriceResolutionService

```php
// Resolve single price
resolve(ProductVariant $variant, PricingContext $context): ?Price

// Resolve bulk prices
resolveBulk(Collection $variants, PricingContext $context): Collection

// Get quantity tiers
getTiers(ProductVariant $variant, PricingContext $context): Collection

// Clear cache
clearCache(ProductVariant $variant): void
clearAllCache(): void
```

### MarketRouteHelper

```php
// URL generation
market_url($path, $market, $locale): string
market_route($name, $params, $market, $locale): string

// Current context
market(): ?Market
MarketRouteHelper::current(): ?Market
MarketRouteHelper::currentLocale(): string

// Switching
switch_market($market, $locale, $returnUrl): string

// Available options
available_markets(): Collection
available_locales($market): array
```

---

## 🆘 Troubleshooting

### Issue: Price not found

```php
// Check if price exists for context
$exists = Price::where('product_variant_id', $variant->id)
    ->forMarket($market->id)
    ->forCurrency('EUR')
    ->active()
    ->exists();

if (!$exists) {
    // Create missing price
}
```

### Issue: Locale fallback not working

```php
// Check fallback chain
$resolver = app(LocaleResolver::class);
$chain = $resolver->getFallbackChain('it_IT');
dd($chain); // Should contain 'it', 'en'

// Check available locales in config
dd(config('app.available_locales'));
```

### Issue: Market context not set

```php
// Verify middleware is active
Route::middleware(['web', 'multimarket'])->group(...);

// Check session
dd(session('market_id'), session('locale'));

// Manually set context
session(['market_id' => 1, 'locale' => 'it_IT']);
```

---

## 📈 Roadmap

- [ ] Currency conversion rate automation
- [ ] Price synchronization from external systems
- [ ] A/B testing per market
- [ ] Dynamic pricing rules
- [ ] Market-specific product variants
- [ ] Geo-IP automatic market detection
- [ ] Multi-warehouse fulfillment routing

---

## 📞 Support

Per domande o issue:
- GitHub Issues: https://github.com/cartinophp/cartino/issues
- Documentation: https://docs.cartinophp.com
