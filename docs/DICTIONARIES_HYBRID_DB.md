# Dictionaries - Hybrid Approach (Config + Database)

## Overview

Cartino usa un **HYBRID APPROACH** per i dictionaries, combinando il meglio di:

1. **CODICE** - Items base sempre presenti, performanti, versionati
2. **CONFIG** - Estensioni via config per dev/deploy
3. **DATABASE** - Items custom gestiti via UI admin runtime

### Merge Priority (dal più basso al più alto):

```
BASE (codice) → CONFIG (cartino.php) → DATABASE (runtime)
```

Se un item esiste in tutte e 3 le fonti, vince il **DATABASE**.

---

## Confronto con Altri Ecommerce

| Ecommerce | Approach | Order Statuses | Payment Providers | Countries |
|-----------|----------|----------------|-------------------|-----------|
| **Shopify** | Hybrid | Hardcoded + Custom | Plugin | Hardcoded |
| **WooCommerce** | Hybrid | Code + Hooks | Plugin | Hardcoded |
| **Magento** | Database | DB (EAV) | DB | DB |
| **Shopware** | Hybrid | Code + DB | Plugin | Code |
| **Cartino** | **Hybrid** | **Code + Config + DB** | **Code + Config + DB** | **Hardcoded** |

**Vantaggi approccio Cartino:**
- ✅ Performance come Shopify (base items in codice)
- ✅ Flessibilità come Magento (DB per custom)
- ✅ Developer-friendly come WooCommerce (config-based)
- ✅ Best of all worlds

---

## Come Funziona

### 1. Base Items (Codice)

**File**: `src/Dictionaries/AddressTypes.php`

```php
protected function getItems(): array
{
    return [
        ['value' => 'billing', 'label' => 'Billing', 'icon' => 'credit-card'],
        ['value' => 'shipping', 'label' => 'Shipping', 'icon' => 'truck'],
        ['value' => 'both', 'label' => 'Both', 'icon' => 'both'],
    ];
}
```

**✅ Sempre presenti, non eliminabili**

### 2. Config Extensions (Deploy-time)

**File**: `config/cartino.php`

```php
'dictionary_extensions' => [
    'address_types' => [
        ['value' => 'warehouse', 'label' => 'Warehouse', 'icon' => 'building'],
    ],
],
```

**✅ Versionati in Git, deployabili**

### 3. Database Items (Runtime)

**Via API Admin**:

```bash
POST /api/data/dictionaries/address_types/items
{
  "value": "office",
  "label": "Office HQ",
  "extra": {
    "icon": "briefcase",
    "description": "Corporate office address"
  }
}
```

**✅ Gestibili da UI admin, no deploy**

### Risultato Finale (Merge Automatico)

```bash
GET /api/data/dictionaries/address_types
```

```json
{
  "items": [
    {"value": "billing", "label": "Billing", "_source": "code"},
    {"value": "shipping", "label": "Shipping", "_source": "code"},
    {"value": "both", "label": "Both", "_source": "code"},
    {"value": "warehouse", "label": "Warehouse", "_source": "config"},
    {"value": "office", "label": "Office HQ", "_source": "database", "_db_id": 1}
  ]
}
```

---

## Database Schema

```sql
CREATE TABLE dictionary_items (
    id BIGINT PRIMARY KEY,
    dictionary VARCHAR(255),              -- Handle (es: address_types)
    value VARCHAR(255),                   -- Item key
    label VARCHAR(255),                   -- Display label
    extra JSON,                           -- Additional fields
    order INT DEFAULT 0,                  -- Sort order
    is_enabled BOOLEAN DEFAULT TRUE,      -- Active/inactive
    is_system BOOLEAN DEFAULT FALSE,      -- Protected item
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    UNIQUE(dictionary, value)
);
```

### Campi Extra (JSON)

Puoi aggiungere qualsiasi campo custom in `extra`:

```json
{
  "icon": "building",
  "color": "blue",
  "description": "Corporate warehouse",
  "requires_appointment": true,
  "business_hours": "9-17",
  "custom_field": "any value"
}
```

---

## API Admin - Gestione Items

### 1. List Custom Items

```bash
GET /api/data/dictionaries/address_types/items
Authorization: Bearer {token}
```

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "value": "warehouse",
      "label": "Warehouse",
      "extra": {"icon": "building"},
      "order": 0,
      "is_enabled": true,
      "is_system": false,
      "created_at": "2025-01-15T10:00:00Z"
    }
  ]
}
```

### 2. Create New Item

```bash
POST /api/data/dictionaries/address_types/items
Authorization: Bearer {token}
Content-Type: application/json

{
  "value": "factory",
  "label": "Factory",
  "extra": {
    "icon": "industry",
    "description": "Manufacturing facility"
  },
  "order": 10
}
```

### 3. Update Item

```bash
PUT /api/data/dictionaries/address_types/items/1
Authorization: Bearer {token}

{
  "label": "Main Warehouse",
  "extra": {
    "icon": "building",
    "is_main": true
  }
}
```

### 4. Delete Item

```bash
DELETE /api/data/dictionaries/address_types/items/1
Authorization: Bearer {token}
```

### 5. Toggle Enabled

```bash
POST /api/data/dictionaries/address_types/items/1/toggle
Authorization: Bearer {token}
```

### 6. Reorder Items

```bash
POST /api/data/dictionaries/address_types/items/reorder
Authorization: Bearer {token}

{
  "items": [
    {"id": 1, "order": 0},
    {"id": 2, "order": 1},
    {"id": 3, "order": 2}
  ]
}
```

---

## Sicurezza

### System Items (Protected)

Items con `is_system = true` non possono essere:
- ❌ Modificati
- ❌ Eliminati
- ❌ Disabilitati

Questi sono gli items **base dal codice**.

### Extensible Dictionaries

Solo i dictionaries in whitelist possono avere items DB:

```php
'extensible_dictionaries' => [
    'address_types',      // ✅ Può avere items DB
    'payment_providers',  // ✅ Può avere items DB
    'shipping_types',     // ✅ Può avere items DB
    'order_statuses',     // ✅ Può avere items DB
    'payment_statuses',   // ✅ Può avere items DB
    'units',              // ✅ Può avere items DB
],
```

**Non estensibili** (dati ISO standard):
- ❌ `countries` - Usa standard ISO 3166
- ❌ `currencies` - Usa standard ISO 4217
- ❌ `languages` - Usa standard ISO 639
- ❌ `vat_rates` - Aliquote ufficiali EU

---

## Use Cases

### Scenario 1: Multi-Tenant SaaS

Ogni tenant ha i suoi custom statuses:

**Tenant A (B2B)**:
```sql
INSERT INTO dictionary_items (dictionary, value, label, extra) VALUES
('order_statuses', 'quote_requested', 'Quote Requested', '{"color": "blue"}'),
('order_statuses', 'awaiting_approval', 'Awaiting Approval', '{"color": "yellow"}');
```

**Tenant B (Retail)**:
```sql
INSERT INTO dictionary_items (dictionary, value, label, extra) VALUES
('order_statuses', 'gift_wrapping', 'Gift Wrapping', '{"color": "purple"}'),
('order_statuses', 'ready_pickup', 'Ready for Pickup', '{"color": "teal"}');
```

### Scenario 2: White Label

Base via config, tenant-specific via DB:

**Config** (tutti i tenant):
```php
'dictionary_extensions' => [
    'payment_providers' => [
        ['value' => 'stripe', 'label' => 'Stripe'],
        ['value' => 'paypal', 'label' => 'PayPal'],
    ],
],
```

**Database** (tenant specifico):
```sql
-- Solo tenant IT
INSERT INTO dictionary_items (dictionary, value, label) VALUES
('payment_providers', 'satispay', 'Satispay');

-- Solo tenant FR
INSERT INTO dictionary_items (dictionary, value, label) VALUES
('payment_providers', 'lydia', 'Lydia');
```

---

## Migration Path

### Da Solo-Config a Hybrid

Se hai già items in config e vuoi spostarli in DB:

**1. Export da config**:
```php
$configItems = config('cartino.dictionary_extensions.address_types');

foreach ($configItems as $item) {
    DictionaryItem::create([
        'dictionary' => 'address_types',
        'value' => $item['value'],
        'label' => $item['label'],
        'extra' => $item,
    ]);
}
```

**2. Rimuovi da config**:
```php
// Prima
'dictionary_extensions' => [
    'address_types' => [
        ['value' => 'warehouse', 'label' => 'Warehouse'],
    ],
],

// Dopo (vuoto, tutto in DB)
'dictionary_extensions' => [],
```

**3. Ora tutto via UI admin** 🎉

---

## Performance

### Caching Strategy

1. **Base items** (codice) - Cached in OPcache (zero query)
2. **Config items** - Cached in config cache
3. **DB items** - Cached in Redis/Memcached con TTL 1h

**Cache invalidation**:
```php
// Auto-cleared dopo create/update/delete
Cache::forget("dictionary:address_types:all");

// Manual clear
php artisan cache:forget dictionary:address_types:all
```

### Query Optimization

**Prima (senza DB)**:
```
0 queries (tutto in memoria)
```

**Dopo (con DB)**:
```
1 query per dictionary (cached per 1h)
SELECT * FROM dictionary_items WHERE dictionary = 'address_types' AND is_enabled = 1
```

**Con eager loading**:
```php
// Carica tutti i dictionaries in 1 query
$allItems = DictionaryItem::whereIn('dictionary', [
    'address_types', 'order_statuses', 'payment_providers'
])->get()->groupBy('dictionary');
```

---

## Best Practices

### 1. Quando Usare Config vs DB

**Usa CONFIG quando**:
- ✅ Items comuni a tutti i tenant
- ✅ Vuoi versionare in Git
- ✅ Deploy automatizzato
- ✅ Sviluppo/staging/production parity

**Usa DATABASE quando**:
- ✅ Items specifici per tenant/customer
- ✅ UI admin per business user
- ✅ Runtime changes senza deploy
- ✅ Audit trail necessario

### 2. Naming Conventions

```php
// ✅ Good
['value' => 'warehouse_main', 'label' => 'Main Warehouse']
['value' => 'pickup_point_store', 'label' => 'Store Pickup Point']

// ❌ Bad
['value' => 'WH1', 'label' => 'wh']
['value' => 'type_1', 'label' => 'Type 1']
```

### 3. Extra Fields Structure

```php
// ✅ Good - Structured and documented
[
    'value' => 'express_overnight',
    'label' => 'Express Overnight',
    'extra' => [
        'icon' => 'zap',                // Icon name
        'color' => 'blue',               // UI color
        'description' => '24h delivery', // Help text
        'metadata' => [                  // Grouped business data
            'max_weight_kg' => 30,
            'price_multiplier' => 2.5,
            'cutoff_time' => '15:00',
        ]
    ]
]

// ❌ Bad - Flat and unclear
[
    'value' => 'express',
    'label' => 'Express',
    'extra' => [
        'val1' => 30,
        'val2' => 2.5,
        'val3' => '15:00'
    ]
]
```

---

## Conclusion

Il sistema **Hybrid** di Cartino combina:

✅ **Performance** - Items base in codice (zero query)
✅ **Flexibility** - Config per deploy
✅ **Dinamicità** - DB per runtime
✅ **Sicurezza** - Whitelist + system protection
✅ **Audit** - Timestamps + soft deletes
✅ **DX** - Best developer experience

È l'approccio usato dai **migliori ecommerce moderni** (Shopify, Shopware, BigCommerce).
