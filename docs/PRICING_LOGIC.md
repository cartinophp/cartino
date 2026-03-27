# 📊 Sistema di Pricing Multisite - Documentazione Completa

## Indice

1. [Architettura](#architettura)
2. [Tabelle del Database](#tabelle-del-database)
3. [Logica di Risoluzione Prezzi](#logica-di-risoluzione-prezzi)
4. [Esempi di Utilizzo](#esempi-di-utilizzo)
5. [Query Performance](#query-performance)
6. [Best Practices](#best-practices)

---

## Architettura

Il sistema di pricing di Cartino è progettato per supportare:
- ✅ **Multisite/Multi-market** (prezzi diversi per ogni sito)
- ✅ **Multi-currency** (prezzi in diverse valute)
- ✅ **Price Lists** (listini prezzi per customer groups)
- ✅ **Quantity Breaks** (prezzi a scaglioni di quantità)
- ✅ **Time-based Pricing** (prezzi con validità temporale)
- ✅ **Tax Configuration** (prezzi con o senza tasse)
- ✅ **Promotional Pricing** (compare-at prices)

### Schema Concettuale

```
Product (1) ──> (N) ProductVariant (1) ──> (N) Price
                                               │
                                               ├──> Site (Market)
                                               ├──> PriceList (Optional)
                                               └──> Currency
```

---

## Tabelle del Database

### 1. `price_lists`

Definisce i listini prezzi (wholesale, retail, promotional, etc.)

```sql
CREATE TABLE price_lists (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),              -- "Wholesale Prices"
    code VARCHAR(255) UNIQUE,       -- "wholesale-2024"
    description TEXT,
    type VARCHAR(255),              -- standard, promotional, wholesale, tier
    priority INT DEFAULT 0,         -- Priority di applicazione (higher = first)
    is_active BOOLEAN,
    starts_at TIMESTAMP NULL,       -- Inizio validità
    ends_at TIMESTAMP NULL,         -- Fine validità
    metadata JSONB,
    created_at, updated_at, deleted_at
);
```

**Tipi di Price List:**
- `standard`: Prezzi base standard
- `promotional`: Prezzi promozionali temporanei
- `wholesale`: Prezzi all'ingrosso
- `tier`: Prezzi a scaglioni (VIP, Gold, Silver, etc.)

### 2. `prices`

Tabella centrale che contiene tutti i prezzi

```sql
CREATE TABLE prices (
    id BIGINT PRIMARY KEY,

    -- References (Composite Key Logic)
    product_variant_id BIGINT FK,        -- Variante del prodotto
    site_id BIGINT FK NULL,              -- Sito (NULL = tutti i siti)
    price_list_id BIGINT FK NULL,        -- Listino (NULL = prezzo base)
    currency VARCHAR(3),                 -- EUR, USD, GBP

    -- Pricing (stored as INTEGER in cents/centesimi)
    amount BIGINT UNSIGNED,              -- 9999 = €99.99
    compare_at_amount BIGINT UNSIGNED NULL, -- Prezzo confronto (barrato)
    cost_amount BIGINT UNSIGNED NULL,    -- Costo (per calcolo margini)

    -- Tax Configuration
    tax_included BOOLEAN DEFAULT false,  -- Prezzo include tasse?
    tax_rate DECIMAL(8,4) NULL,          -- 22.0000 = 22%

    -- Quantity Breaks
    min_quantity INT DEFAULT 1,          -- Quantità minima
    max_quantity INT NULL,               -- Quantità massima (NULL = infinito)

    -- Validity Period
    starts_at TIMESTAMP NULL,
    ends_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT true,

    -- Metadata
    metadata JSONB,
    created_at, updated_at, deleted_at,

    -- FUNDAMENTAL INDEX
    UNIQUE (product_variant_id, site_id, price_list_id, currency, min_quantity)
);
```

**Perché INTEGER per i prezzi?**
- ✅ Precisione matematica assoluta (no floating point errors)
- ✅ Performance nelle query e ordinamenti
- ✅ Standard industry (Stripe, Shopify, ecc.)

**Formato:** `€99.99` → stored as `9999` centesimi

### 3. `customer_group_price_list`

Assegna listini prezzi ai customer groups

```sql
CREATE TABLE customer_group_price_list (
    id BIGINT PRIMARY KEY,
    customer_group_id BIGINT FK,
    price_list_id BIGINT FK,
    priority INT DEFAULT 0,         -- Priority di questo listino per il gruppo
    created_at, updated_at,

    UNIQUE (customer_group_id, price_list_id)
);
```

---

## Logica di Risoluzione Prezzi

### Algoritmo di Price Resolution

Quando un cliente richiede il prezzo di un prodotto, il sistema segue questa logica **a cascata** (fallback):

```php
function resolvePrice(
    ProductVariant $variant,
    Site $site,
    string $currency,
    int $quantity,
    ?Customer $customer = null
): ?Price {
    // 1. Identificare i price lists applicabili al customer
    $priceLists = [];
    if ($customer) {
        $priceLists = $customer->customerGroups()
            ->with('priceLists')
            ->get()
            ->flatMap->priceLists
            ->unique('id')
            ->sortByDesc('priority'); // Higher priority first
    }

    // 2. Query con FALLBACK LOGIC
    $query = Price::query()
        ->where('product_variant_id', $variant->id)
        ->where('currency', $currency)
        ->where('min_quantity', '<=', $quantity)
        ->where(fn($q) => $q->whereNull('max_quantity')
                           ->orWhere('max_quantity', '>=', $quantity))
        ->active() // is_active=true && within validity period
        ->orderByRaw('
            CASE
                WHEN site_id = ? AND price_list_id IS NOT NULL THEN 1
                WHEN site_id = ? AND price_list_id IS NULL THEN 2
                WHEN site_id IS NULL AND price_list_id IS NOT NULL THEN 3
                WHEN site_id IS NULL AND price_list_id IS NULL THEN 4
            END
        ', [$site->id, $site->id]);

    // 3. Try with price lists first
    foreach ($priceLists as $priceList) {
        $price = (clone $query)
            ->where(fn($q) => $q->where('site_id', $site->id)
                                ->orWhereNull('site_id'))
            ->where('price_list_id', $priceList->id)
            ->first();

        if ($price) {
            return $price;
        }
    }

    // 4. Fallback to base price (no price list)
    return (clone $query)
        ->where(fn($q) => $q->where('site_id', $site->id)
                            ->orWhereNull('site_id'))
        ->whereNull('price_list_id')
        ->first();
}
```

### Priority Resolution Matrix

| Priority | Site ID | Price List | Customer Group | Scenario |
|----------|---------|------------|----------------|----------|
| **1** | Specific | Specific | Yes | VIP customer, specific site, special pricing |
| **2** | Specific | NULL | No | Site-specific base pricing |
| **3** | NULL | Specific | Yes | Global price list for customer group |
| **4** | NULL | NULL | No | **Global fallback price** |

**Esempio pratico:**

```
Customer: John Doe (VIP Customer Group)
Site: IT (Italy)
Currency: EUR
Variant: T-Shirt Size M (#123)
Quantity: 5

Resolution Steps:
1. Check: variant_id=123, site_id=IT, price_list_id=VIP, EUR, qty=5 → ✅ FOUND €45.00
2. No need to fallback, use VIP price!

Customer: Guest (No Customer Group)
Same parameters...

Resolution Steps:
1. Check: variant_id=123, site_id=IT, price_list_id=NULL, EUR, qty=5 → ✅ FOUND €59.99
2. Use base price for Italy
```

---

## Esempi di Utilizzo

### Scenario 1: Prezzo Base Globale

```php
// Creare un prezzo base globale (valido per tutti i siti)
Price::create([
    'product_variant_id' => 123,
    'site_id' => null,              // NULL = tutti i siti
    'price_list_id' => null,        // NULL = prezzo base
    'currency' => 'EUR',
    'amount' => 9999,               // €99.99
    'tax_included' => false,
    'tax_rate' => 22.00,
    'is_active' => true,
]);
```

### Scenario 2: Prezzo Specifico per Sito (Market-Specific)

```php
// Italia: €89.99 (tasse incluse)
Price::create([
    'product_variant_id' => 123,
    'site_id' => 1,                // Site IT
    'price_list_id' => null,
    'currency' => 'EUR',
    'amount' => 8999,              // €89.99
    'tax_included' => true,        // Prezzo IVA inclusa
    'tax_rate' => 22.00,
    'is_active' => true,
]);

// USA: $120.00 (tasse escluse)
Price::create([
    'product_variant_id' => 123,
    'site_id' => 2,                // Site US
    'price_list_id' => null,
    'currency' => 'USD',
    'amount' => 12000,             // $120.00
    'tax_included' => false,       // Sales tax calcolata al checkout
    'tax_rate' => null,
    'is_active' => true,
]);
```

### Scenario 3: Price List per Customer Group (Wholesale)

```php
// Creare un listino "Wholesale"
$wholesalePriceList = PriceList::create([
    'name' => 'Wholesale Pricing',
    'code' => 'wholesale-2024',
    'type' => 'wholesale',
    'priority' => 10,
    'is_active' => true,
]);

// Assegnare al customer group "Resellers"
$resellerGroup->priceLists()->attach($wholesalePriceList->id, ['priority' => 10]);

// Creare prezzi wholesale
Price::create([
    'product_variant_id' => 123,
    'site_id' => null,
    'price_list_id' => $wholesalePriceList->id,
    'currency' => 'EUR',
    'amount' => 6999,              // €69.99 (wholesale)
    'compare_at_amount' => 9999,   // €99.99 (retail price)
    'tax_included' => false,
    'is_active' => true,
]);
```

### Scenario 4: Quantity Breaks (Scaglioni)

```php
// 1-9 pezzi: €99.99
Price::create([
    'product_variant_id' => 123,
    'currency' => 'EUR',
    'amount' => 9999,
    'min_quantity' => 1,
    'max_quantity' => 9,
]);

// 10-49 pezzi: €89.99
Price::create([
    'product_variant_id' => 123,
    'currency' => 'EUR',
    'amount' => 8999,
    'min_quantity' => 10,
    'max_quantity' => 49,
]);

// 50+ pezzi: €79.99
Price::create([
    'product_variant_id' => 123,
    'currency' => 'EUR',
    'amount' => 7999,
    'min_quantity' => 50,
    'max_quantity' => null,        // NULL = infinito
]);
```

### Scenario 5: Promotional Pricing (Time-Limited)

```php
// Black Friday Sale
$blackFridayList = PriceList::create([
    'name' => 'Black Friday 2024',
    'code' => 'black-friday-2024',
    'type' => 'promotional',
    'priority' => 100,             // Highest priority
    'is_active' => true,
    'starts_at' => '2024-11-29 00:00:00',
    'ends_at' => '2024-12-01 23:59:59',
]);

Price::create([
    'product_variant_id' => 123,
    'site_id' => null,
    'price_list_id' => $blackFridayList->id,
    'currency' => 'EUR',
    'amount' => 4999,              // €49.99 (50% OFF!)
    'compare_at_amount' => 9999,   // €99.99 (original price)
    'starts_at' => '2024-11-29 00:00:00',
    'ends_at' => '2024-12-01 23:59:59',
    'is_active' => true,
]);
```

---

## Query Performance

### Indice Fondamentale

```sql
CREATE UNIQUE INDEX prices_unique_idx
ON prices (product_variant_id, site_id, price_list_id, currency, min_quantity);
```

**Questo indice garantisce:**
- ✅ **Unicità** dei prezzi (no duplicati)
- ✅ **Performance** nelle query di risoluzione prezzi
- ✅ **Covering Index** per la maggior parte delle query

### Query Optimization Tips

1. **Eager Loading dei Prices**
```php
// ✅ GOOD (1 query)
$variants = ProductVariant::with([
    'prices' => fn($q) => $q->active()
        ->forSite($siteId)
        ->forCurrency($currency)
])-> get();

// ❌ BAD (N+1 queries)
$variants = ProductVariant::all();
foreach ($variants as $variant) {
    $price = $variant->prices()->active()->first(); // N queries!
}
```

2. **Cache dei Prezzi Calcolati**
```php
// Cache resolved prices per 1 hour
$cacheKey = "price:{$variant->id}:{$site->id}:{$currency}:{$customer->id}:{$quantity}";
$price = Cache::remember($cacheKey, 3600, fn() =>
    $this->priceResolver->resolve($variant, $site, $currency, $quantity, $customer)
);
```

3. **Batch Price Calculations**
```php
// Per carrelli con molti prodotti
$variantIds = $cart->items->pluck('variant_id');
$prices = Price::whereIn('product_variant_id', $variantIds)
    ->active()
    ->forSite($siteId)
    ->forCurrency($currency)
    ->get()
    ->keyBy('product_variant_id');
```

---

## Best Practices

### 1. Gestione Tasse (Tax Handling)

**Europa (IVA Inclusa):**
```php
Price::create([
    'amount' => 12200,           // €122.00 (con IVA 22%)
    'tax_included' => true,
    'tax_rate' => 22.00,
]);

// Calcolo prezzo netto:
$netAmount = (int)round($price->amount / (1 + ($price->tax_rate / 100)));
// €122.00 / 1.22 = €100.00 netto
```

**USA/Canada (Tax Esclusa):**
```php
Price::create([
    'amount' => 10000,           // $100.00 (senza tax)
    'tax_included' => false,
    'tax_rate' => null,          // Tax calcolata al checkout
]);
```

### 2. Currency Conversion

**NON** memorizzare conversioni automatiche! Ogni prezzo deve essere impostato manualmente:

```php
// ❌ WRONG - Don't auto-convert
$usdPrice = $eurPrice * $exchangeRate;

// ✅ CORRECT - Set prices manually per currency
Price::create(['currency' => 'EUR', 'amount' => 9999]);
Price::create(['currency' => 'USD', 'amount' => 10999]); // Not exact conversion!
```

### 3. Varianti Senza Opzioni (Come Shopify)

Ogni prodotto DEVE avere almeno una variante, anche se non ha opzioni:

```php
// Product senza variant options
$product = Product::create(['title' => 'Simple T-Shirt']);

// ALWAYS create a default variant
$variant = ProductVariant::create([
    'product_id' => $product->id,
    'title' => 'Default',
    'sku' => 'TSHIRT-001',
    'is_default' => true,
]);

// Set price for the default variant
Price::create([
    'product_variant_id' => $variant->id,
    'currency' => 'EUR',
    'amount' => 2999, // €29.99
]);
```

### 4. Evitare Prezzi Duplicati

L'indice UNIQUE previene duplicati, ma fare controllo applicativo:

```php
$existingPrice = Price::where([
    'product_variant_id' => $variantId,
    'site_id' => $siteId,
    'price_list_id' => $priceListId,
    'currency' => $currency,
    'min_quantity' => $minQty,
])->first();

if ($existingPrice) {
    $existingPrice->update(['amount' => $newAmount]);
} else {
    Price::create([/* ... */]);
}
```

### 5. Audit Trail per Price Changes

```php
// Soft delete mantiene history
Price::create([/* old price */]);
Price::find($oldPriceId)->delete(); // Soft delete

// Oppure usare metadata per tracking
Price::create([
    'amount' => 7999,
    'metadata' => [
        'previous_amount' => 9999,
        'changed_by' => auth()->id(),
        'reason' => 'Seasonal promotion',
    ],
]);
```

---

## Riassunto Chiavi Design

| Feature | Implementation |
|---------|---------------|
| **Multisite** | `site_id` NULL = global, specific = site-only |
| **Price Lists** | `price_list_id` NULL = base, specific = listino |
| **Precision** | INTEGER cents (9999 = €99.99) |
| **Tax Handling** | `tax_included` boolean + `tax_rate` decimal |
| **Quantity Breaks** | `min_quantity` & `max_quantity` |
| **Time-based** | `starts_at` & `ends_at` timestamps |
| **Priority** | Cascade logic: site+list > site > list > global |
| **Performance** | Composite UNIQUE index on 5 columns |

---

**Versione:** 1.0.0
**Ultima modifica:** 2025-12-13
**Autore:** Cartino Development Team
