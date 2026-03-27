# Dictionaries - Sistema di Estensione

## Overview

I Dictionaries di Cartino sono **completamente estensibili**. Puoi:

1. **Aggiungere items custom** a dictionaries esistenti
2. **Creare dictionaries completamente nuovi**
3. **Sovrascrivere dictionaries** esistenti

Tutto tramite configurazione, senza toccare il codice core.

---

## Nuovi Endpoints (sotto /data)

Tutti i dictionaries sono ora disponibili sotto `/api/data`:

```bash
GET /api/data                              # Panoramica data endpoints
GET /api/data/dictionaries                 # Lista tutti dictionaries
GET /api/data/dictionaries/search?q=...    # Ricerca globale
GET /api/data/dictionaries/{handle}        # Ottieni dictionary
GET /api/data/dictionaries/{handle}/{key}  # Ottieni item specifico
```

---

## Estendere Dictionaries Esistenti

### 1. Address Types Custom

**Caso d'uso**: Hai bisogno di tipi indirizzo custom come "Warehouse", "Office", etc.

**Config** (`config/cartino.php`):

```php
'dictionary_extensions' => [
    'address_types' => [
        ['value' => 'warehouse', 'label' => 'Warehouse', 'icon' => 'building'],
        ['value' => 'office', 'label' => 'Office HQ', 'icon' => 'briefcase'],
        ['value' => 'pickup_point', 'label' => 'Pickup Point', 'icon' => 'map-pin'],
    ],
],
```

**Risultato API**:

```bash
GET /api/data/dictionaries/address_types
```

```json
{
  "success": true,
  "data": {
    "handle": "address_types",
    "is_extensible": true,
    "has_custom_items": true,
    "items": [
      {
        "value": "billing",
        "label": "Billing Address",
        "extra": {
          "value": "billing",
          "label": "Billing Address",
          "icon": "credit-card"
        }
      },
      {
        "value": "shipping",
        "label": "Shipping Address",
        "extra": {
          "value": "shipping",
          "label": "Shipping Address",
          "icon": "truck"
        }
      },
      {
        "value": "both",
        "label": "Billing & Shipping",
        "extra": {
          "value": "both",
          "label": "Billing & Shipping",
          "icon": "both"
        }
      },
      {
        "value": "warehouse",
        "label": "Warehouse",
        "extra": {
          "value": "warehouse",
          "label": "Warehouse",
          "icon": "building"
        }
      },
      {
        "value": "office",
        "label": "Office HQ",
        "extra": {
          "value": "office",
          "label": "Office HQ",
          "icon": "briefcase"
        }
      },
      {
        "value": "pickup_point",
        "label": "Pickup Point",
        "extra": {
          "value": "pickup_point",
          "label": "Pickup Point",
          "icon": "map-pin"
        }
      }
    ],
    "total": 6
  }
}
```

---

### 2. Order Statuses Custom

**Caso d'uso**: Aggiungi stati ordine specifici per il tuo business.

**Config**:

```php
'dictionary_extensions' => [
    'order_statuses' => [
        ['value' => 'on_hold', 'label' => 'On Hold', 'color' => 'orange', 'icon' => 'pause'],
        ['value' => 'backordered', 'label' => 'Backordered', 'color' => 'purple', 'icon' => 'clock'],
        ['value' => 'ready_pickup', 'label' => 'Ready for Pickup', 'color' => 'teal', 'icon' => 'package'],
    ],
],
```

**Uso nel frontend**:

```javascript
const statuses = await fetch('/api/data/dictionaries/order_statuses');

// Filtra gli stati custom
const customStatuses = statuses.data.items.filter(
  item => ['on_hold', 'backordered', 'ready_pickup'].includes(item.value)
);

// Render badges
customStatuses.map(status => (
  <Badge color={status.extra.color} icon={status.extra.icon}>
    {status.label}
  </Badge>
));
```

---

### 3. Payment Providers Custom

**Caso d'uso**: Aggiungi gateway di pagamento locali o custom.

**Config**:

```php
'dictionary_extensions' => [
    'payment_providers' => [
        // Gateway italiano
        ['value' => 'nexi', 'label' => 'Nexi', 'supports' => ['card'], 'countries' => ['IT']],

        // Gateway custom
        ['value' => 'custom_gateway', 'label' => 'My Custom Gateway', 'supports' => ['card', 'wallet'], 'countries' => ['*']],

        // Crypto payment
        ['value' => 'coinbase', 'label' => 'Coinbase Commerce', 'supports' => ['crypto'], 'countries' => ['*']],
    ],
],
```

---

### 4. Units of Measure Custom

**Caso d'uso**: Aggiungi unità di misura specifiche (pezzi, scatole, pallet, etc.).

**Config**:

```php
'dictionary_extensions' => [
    'units' => [
        ['value' => 'pz', 'label' => 'Pezzi', 'type' => 'quantity', 'system' => 'metric', 'symbol' => 'pz'],
        ['value' => 'box', 'label' => 'Scatole', 'type' => 'packaging', 'system' => 'generic', 'symbol' => 'box'],
        ['value' => 'pallet', 'label' => 'Pallet', 'type' => 'packaging', 'system' => 'generic', 'symbol' => 'plt'],
        ['value' => 'roll', 'label' => 'Rotoli', 'type' => 'packaging', 'system' => 'generic', 'symbol' => 'roll'],
    ],
],
```

---

### 5. Shipping Types Custom

**Caso d'uso**: Metodi di spedizione specifici.

**Config**:

```php
'dictionary_extensions' => [
    'shipping_types' => [
        ['value' => 'same_day', 'label' => 'Same Day Delivery', 'description' => 'Delivered within 24h'],
        ['value' => 'refrigerated', 'label' => 'Refrigerated Transport', 'description' => 'For perishable goods'],
        ['value' => 'white_glove', 'label' => 'White Glove Service', 'description' => 'Premium delivery with setup'],
    ],
],
```

---

## Creare Dictionary Completamente Nuovo

### 1. Crea la Classe

**File**: `app/Dictionaries/ProductCategories.php`

```php
<?php

namespace App\Dictionaries;

use Cartino\Dictionaries\BasicDictionary;

class ProductCategories extends BasicDictionary
{
    protected array $keywords = ['product', 'category', 'catalog'];

    protected array $searchable = ['value', 'label', 'description'];

    protected function getItems(): array
    {
        return [
            [
                'value' => 'electronics',
                'label' => 'Electronics',
                'icon' => 'cpu',
                'description' => 'Electronic devices and accessories',
                'vat_rate' => 22,
            ],
            [
                'value' => 'clothing',
                'label' => 'Clothing & Fashion',
                'icon' => 'shirt',
                'description' => 'Apparel and fashion items',
                'vat_rate' => 22,
            ],
            [
                'value' => 'food',
                'label' => 'Food & Beverage',
                'icon' => 'coffee',
                'description' => 'Food products and drinks',
                'vat_rate' => 10, // Aliquota ridotta
            ],
            [
                'value' => 'books',
                'label' => 'Books & Media',
                'icon' => 'book',
                'description' => 'Books, magazines, and media',
                'vat_rate' => 4, // Aliquota super ridotta
            ],
        ];
    }
}
```

### 2. Registra nel Config

**File**: `config/cartino.php`

```php
'custom_dictionaries' => [
    'product_categories' => \App\Dictionaries\ProductCategories::class,
    'warehouse_locations' => \App\Dictionaries\WarehouseLocations::class,
    'custom_tags' => \App\Dictionaries\CustomTags::class,
],
```

### 3. Usa via API

```bash
GET /api/data/dictionaries/product_categories
```

```json
{
  "success": true,
  "data": {
    "handle": "product_categories",
    "title": "Product Categories",
    "keywords": ["product", "category", "catalog"],
    "items": [
      {
        "value": "electronics",
        "label": "Electronics",
        "extra": {
          "value": "electronics",
          "label": "Electronics",
          "icon": "cpu",
          "description": "Electronic devices and accessories",
          "vat_rate": 22
        }
      }
    ],
    "total": 4
  }
}
```

---

## Dictionaries Estensibili

Solo alcuni dictionaries possono essere estesi per motivi di sicurezza:

```php
'extensible_dictionaries' => [
    'address_types',      // ✅ Estensibile
    'payment_providers',  // ✅ Estensibile
    'shipping_types',     // ✅ Estensibile
    'order_statuses',     // ✅ Estensibile
    'payment_statuses',   // ✅ Estensibile
    'units',              // ✅ Estensibile
],

// Questi NON sono estensibili (dati statici di sistema):
// - countries (usa i dati ISO standard)
// - currencies (usa i codici ISO 4217)
// - languages (usa i codici ISO 639)
// - timezones (usa i timezone PHP)
// - vat_rates (aliquote ufficiali UE)
```

---

## Verificare Estensibilità

Ogni dictionary API response include flag di estensibilità:

```json
{
  "handle": "address_types",
  "is_extensible": true,
  "has_custom_items": true,
  "custom_items": 3
}
```

Per verificare se un item è custom:

```bash
GET /api/data/dictionaries/address_types/warehouse
```

```json
{
  "value": "warehouse",
  "label": "Warehouse",
  "extra": {...},
  "is_custom": true
}
```

---

## Esempi Pratici

### Scenario 1: E-commerce B2B

Hai bisogno di tipi indirizzo custom per gestire magazzini e uffici:

```php
'dictionary_extensions' => [
    'address_types' => [
        ['value' => 'warehouse', 'label' => 'Warehouse', 'icon' => 'building'],
        ['value' => 'office', 'label' => 'Corporate Office', 'icon' => 'briefcase'],
        ['value' => 'factory', 'label' => 'Factory', 'icon' => 'industry'],
    ],

    'shipping_types' => [
        ['value' => 'pallet', 'label' => 'Pallet Shipping', 'description' => 'Full pallet delivery'],
        ['value' => 'ltl', 'label' => 'LTL Freight', 'description' => 'Less than truckload'],
    ],

    'order_statuses' => [
        ['value' => 'quote_requested', 'label' => 'Quote Requested', 'color' => 'blue', 'icon' => 'file-text'],
        ['value' => 'quote_sent', 'label' => 'Quote Sent', 'color' => 'indigo', 'icon' => 'send'],
    ],
],
```

### Scenario 2: Marketplace Multi-Vendor

Custom stati per vendors:

```php
'dictionary_extensions' => [
    'order_statuses' => [
        ['value' => 'awaiting_vendor', 'label' => 'Awaiting Vendor Confirmation', 'color' => 'yellow', 'icon' => 'user-check'],
        ['value' => 'vendor_processing', 'label' => 'Vendor Processing', 'color' => 'blue', 'icon' => 'package'],
        ['value' => 'vendor_shipped', 'label' => 'Shipped by Vendor', 'color' => 'green', 'icon' => 'truck'],
    ],

    'payment_providers' => [
        ['value' => 'escrow', 'label' => 'Escrow Service', 'supports' => ['escrow'], 'countries' => ['*']],
    ],
],
```

### Scenario 3: Subscription Box

Stati specifici per subscriptions:

```php
'dictionary_extensions' => [
    'order_statuses' => [
        ['value' => 'subscription_active', 'label' => 'Subscription Active', 'color' => 'green', 'icon' => 'repeat'],
        ['value' => 'subscription_paused', 'label' => 'Subscription Paused', 'color' => 'orange', 'icon' => 'pause'],
        ['value' => 'renewal_pending', 'label' => 'Renewal Pending', 'color' => 'yellow', 'icon' => 'clock'],
    ],

    'shipping_types' => [
        ['value' => 'monthly_box', 'label' => 'Monthly Subscription Box', 'description' => 'Recurring monthly delivery'],
        ['value' => 'quarterly_box', 'label' => 'Quarterly Box', 'description' => 'Recurring quarterly delivery'],
    ],
],
```

---

## Cache Management

Le estensioni sono cached automaticamente. Per invalidare:

```php
// Via artisan
php artisan cache:forget dictionary:address_types:all

// Via code
Cache::forget('dictionary:address_types:all');

// Clear all dictionaries
Cache::tags(['dictionaries'])->flush();
```

---

## Best Practices

### 1. Naming Conventions

```php
// ✅ Good
['value' => 'warehouse', 'label' => 'Warehouse']
['value' => 'ready_for_pickup', 'label' => 'Ready for Pickup']

// ❌ Bad
['value' => 'WH', 'label' => 'wh']
['value' => 'Status1', 'label' => 'status 1']
```

### 2. Provide Rich Metadata

```php
// ✅ Good - Rich metadata
[
    'value' => 'express_overnight',
    'label' => 'Express Overnight',
    'description' => 'Delivered next business day',
    'icon' => 'zap',
    'price_multiplier' => 2.5,
    'max_weight_kg' => 30,
]

// ❌ Basic - Missing context
['value' => 'express', 'label' => 'Express']
```

### 3. Use Consistent Icons

```php
// Usa icon set consistente (es: Heroicons, Feather, etc.)
'address_types' => [
    ['value' => 'warehouse', 'icon' => 'building-office'],      // ✅ Heroicons
    ['value' => 'office', 'icon' => 'building-office-2'],       // ✅ Heroicons
],
```

### 4. Localization

```php
// Usa translation keys per labels
[
    'value' => 'warehouse',
    'label' => __('dictionaries.address_types.warehouse'),
    'icon' => 'building',
]

// resources/lang/it/dictionaries.php
return [
    'address_types' => [
        'warehouse' => 'Magazzino',
        'office' => 'Ufficio',
    ],
];
```

---

## Limitazioni

1. **Non tutti i dictionaries sono estensibili** - Solo quelli in `extensible_dictionaries`
2. **No override completo** - Gli items base rimangono sempre presenti
3. **Config-based only** - Non c'è UI admin per gestire extensions (per ora)
4. **Cache invalidation** - Richiede clear cache dopo modifiche config

---

## Prossimi Sviluppi

Features pianificate:

- [ ] UI Admin per gestire dictionary extensions
- [ ] Database-backed custom items (oltre al config)
- [ ] Import/Export dictionary extensions
- [ ] Dictionary versioning
- [ ] API per creare custom items runtime

---

## Migration da DictionariesController

Se stavi usando `/api/dictionaries`, aggiorna a `/api/data/dictionaries`:

```diff
- GET /api/dictionaries
+ GET /api/data/dictionaries

- GET /api/dictionaries/countries
+ GET /api/data/dictionaries/countries

- GET /api/dictionaries/search?q=italy
+ GET /api/data/dictionaries/search?q=italy
```

Le vecchie rotte sono **deprecate** e saranno rimosse nella v2.0.
