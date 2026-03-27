# Dictionaries System

## Overview

Il sistema Dictionaries di Cartino fornisce un modo estensibile per gestire dati di riferimento statici (reference data) utilizzabili sia nel Control Panel che via API. Ispirato a Statamic CMS, permette di centralizzare e standardizzare liste come paesi, valute, lingue, stati degli ordini, etc.

---

## Available Dictionaries

| Handle | Description | Count | Use Cases |
|--------|-------------|-------|-----------|
| `countries` | Paesi con stati/province | 30+ | Address forms, shipping zones, tax calculations |
| `currencies` | Valute mondiali con simboli | 140+ | Multi-currency stores, pricing |
| `languages` | Lingue disponibili | 15+ | Translation, localization |
| `locales` | Locale con regioni | 10+ | Full locale codes (it_IT, en_US) |
| `timezones` | Fusi orari PHP | 400+ | Event scheduling, user preferences |
| `phone_prefixes` | Prefissi telefonici internazionali | 30+ | Phone number validation, autocomplete |
| `address_types` | Tipi di indirizzo | 3 | Address forms |
| `payment_providers` | Provider di pagamento | 8 | Payment gateway selection |
| `shipping_types` | Tipi di spedizione | 5 | Shipping method configuration |
| `order_statuses` | Stati ordine | 7 | Order management, filters |
| `payment_statuses` | Stati pagamento | 6 | Transaction tracking |
| `vat_rates` | Aliquote IVA EU | 18 | Tax calculations |
| `units` | Unità di misura | 13 | Product dimensions, weight |
| `entities` | Model references | Variable | Internal system references |

---

## API Endpoints

### 1. List All Dictionaries

```
GET /api/dictionaries
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "handle": "countries",
      "title": "Countries",
      "keywords": ["countries", "country", "nation", "geography"],
      "count": 30
    },
    {
      "handle": "currencies",
      "title": "Currencies",
      "keywords": ["currencies", "currency", "money", "dollar"],
      "count": 142
    }
  ]
}
```

---

### 2. Get Dictionary Options

```
GET /api/dictionaries/{handle}
```

**Parameters:**
- `search` (optional): Filter results by search term

**Example:**
```bash
GET /api/dictionaries/countries
GET /api/dictionaries/countries?search=italy
```

**Response:**
```json
{
  "success": true,
  "data": {
    "handle": "countries",
    "title": "Countries",
    "keywords": ["countries", "country", "nation", "geography"],
    "options": {
      "IT": "Italy (IT)",
      "US": "United States (US)",
      "GB": "United Kingdom (GB)"
    },
    "items": [
      {
        "value": "IT",
        "label": "Italy (IT)",
        "extra": {
          "code": "IT",
          "name": "Italy",
          "native": "Italia",
          "phone": "+39",
          "capital": "Rome",
          "currency": "EUR",
          "region": "Europe",
          "subregion": "Southern Europe",
          "emoji": "🇮🇹",
          "states": [
            {"code": "LAZ", "name": "Lazio"},
            {"code": "LOM", "name": "Lombardia"}
          ]
        }
      }
    ],
    "total": 30
  }
}
```

---

### 3. Get Specific Item

```
GET /api/dictionaries/{handle}/{key}
```

**Example:**
```bash
GET /api/dictionaries/countries/IT
GET /api/dictionaries/currencies/EUR
GET /api/dictionaries/phone_prefixes/IT
```

**Response:**
```json
{
  "success": true,
  "data": {
    "value": "IT",
    "label": "Italy (IT)",
    "extra": {
      "code": "IT",
      "name": "Italy",
      "native": "Italia",
      "phone": "+39",
      "capital": "Rome",
      "currency": "EUR",
      "region": "Europe",
      "subregion": "Southern Europe",
      "emoji": "🇮🇹",
      "states": [...]
    }
  }
}
```

---

### 4. Search Across Dictionaries

```
GET /api/dictionaries/search?q={query}
```

**Example:**
```bash
GET /api/dictionaries/search?q=euro
```

**Response:**
```json
{
  "success": true,
  "data": {
    "query": "euro",
    "results": [
      {
        "handle": "currencies",
        "title": "Currencies",
        "items": [
          {
            "value": "EUR",
            "label": "Euro (EUR)",
            "extra": {...}
          }
        ],
        "count": 1
      },
      {
        "handle": "countries",
        "title": "Countries",
        "items": [
          {
            "value": "IT",
            "label": "Italy (IT)",
            "extra": {...}
          }
        ],
        "count": 18
      }
    ],
    "total_dictionaries": 2,
    "total_items": 19
  }
}
```

---

## Dictionary Details

### Countries

**Handle:** `countries`

**Fields:**
- `code` - ISO 2-letter country code (IT, US, GB)
- `name` - English name
- `native` - Native language name
- `phone` - Phone prefix (+39, +1, +44)
- `capital` - Capital city
- `currency` - Currency code (EUR, USD, GBP)
- `region` - Continent/region
- `subregion` - Sub-region
- `emoji` - Flag emoji
- `states` - Array of states/provinces (for IT, US, DE, CA, AU)

**Use Cases:**
```javascript
// Address autocomplete
const countries = await fetch('/api/dictionaries/countries');
const italy = await fetch('/api/dictionaries/countries/IT');
const italianStates = italy.data.extra.states;

// Shipping zones
const europeanCountries = await fetch('/api/dictionaries/countries?search=europe');

// Phone validation
const phonePrefix = italy.data.extra.phone; // "+39"
```

---

### Currencies

**Handle:** `currencies`

**Fields:**
- `code` - ISO 4217 currency code (EUR, USD, GBP)
- `name` - Currency name
- `symbol` - Currency symbol (€, $, £)
- `decimals` - Decimal places (0, 2, or 3)

**Use Cases:**
```javascript
// Multi-currency store
const currencies = await fetch('/api/dictionaries/currencies');

// Price formatting
const eur = await fetch('/api/dictionaries/currencies/EUR');
console.log(eur.data.extra.symbol); // "€"
console.log(eur.data.extra.decimals); // 2

// Currency converter
const usd = await fetch('/api/dictionaries/currencies/USD');
```

---

### Languages & Locales

**Handle:** `languages` or `locales`

**Languages Fields:**
- `code` - ISO 639-1 code (it, en, de)
- `name` - English name
- `native` - Native name
- `rtl` - Right-to-left flag

**Locales Fields:**
- `code` - Full locale code (it_IT, en_US)
- `name` - Full name
- `language` - Language name
- `country` - Country name

**Use Cases:**
```javascript
// Language switcher
const languages = await fetch('/api/dictionaries/languages');

// Full locale configuration
const locales = await fetch('/api/dictionaries/locales');

// RTL detection
const arabic = await fetch('/api/dictionaries/languages/ar');
if (arabic.data.extra.rtl) {
  document.dir = 'rtl';
}
```

---

### Phone Prefixes

**Handle:** `phone_prefixes`

**Fields:**
- `code` - Country code (IT, US, GB)
- `country` - Country name
- `prefix` - International prefix (+39, +1, +44)

**Use Cases:**
```javascript
// Phone input autocomplete
const prefixes = await fetch('/api/dictionaries/phone_prefixes');

// Validation
const italyPrefix = await fetch('/api/dictionaries/phone_prefixes/IT');
console.log(italyPrefix.data.extra.prefix); // "+39"

// Auto-detect country from prefix
const searchResult = await fetch('/api/dictionaries/phone_prefixes?search=+39');
```

---

### VAT Rates (European)

**Handle:** `vat_rates`

**Fields:**
- `country` - Country code
- `name` - Country name
- `rate` - Standard VAT rate percentage
- `reduced_rates` - Array of reduced rates

**Use Cases:**
```javascript
// Tax calculation for EU
const vatRates = await fetch('/api/dictionaries/vat_rates');

// Get Italy's VAT
const italyVat = await fetch('/api/dictionaries/vat_rates/IT');
console.log(italyVat.data.extra.rate); // 22
console.log(italyVat.data.extra.reduced_rates); // [10, 5, 4]

// Calculate tax
const price = 100;
const vatRate = italyVat.data.extra.rate;
const taxAmount = price * (vatRate / 100);
```

---

### Order & Payment Statuses

**Handle:** `order_statuses` or `payment_statuses`

**Fields:**
- `value` - Status code
- `label` - Localized label
- `color` - Color for UI (gray, blue, green, red, yellow, etc.)
- `icon` - Icon name (optional)

**Use Cases:**
```javascript
// Order status dropdown
const statuses = await fetch('/api/dictionaries/order_statuses');

// Status badge
const pending = await fetch('/api/dictionaries/order_statuses/pending');
<Badge color={pending.data.extra.color}>
  {pending.data.label}
</Badge>

// Filter options
const filters = statuses.data.items.map(item => ({
  value: item.value,
  label: item.label
}));
```

---

### Units of Measure

**Handle:** `units`

**Fields:**
- `value` - Unit code (kg, lb, cm, in)
- `label` - Full name
- `type` - Type (weight, length, volume)
- `system` - Measurement system (metric, imperial)
- `symbol` - Display symbol

**Use Cases:**
```javascript
// Product dimensions form
const units = await fetch('/api/dictionaries/units');

// Filter by type
const weightUnits = units.data.items.filter(
  item => item.extra.type === 'weight'
);

// Conversion
const kg = await fetch('/api/dictionaries/units/kg');
const lb = await fetch('/api/dictionaries/units/lb');
```

---

## Frontend Usage Examples

### React/Next.js

```typescript
// hooks/useDictionary.ts
import useSWR from 'swr';

export function useDictionary(handle: string, search?: string) {
  const url = `/api/dictionaries/${handle}${search ? `?search=${search}` : ''}`;
  const { data, error } = useSWR(url, fetcher);

  return {
    options: data?.data?.options,
    items: data?.data?.items,
    isLoading: !error && !data,
    isError: error
  };
}

// Component
function CountrySelector() {
  const { items, isLoading } = useDictionary('countries');

  if (isLoading) return <Spinner />;

  return (
    <Select>
      {items?.map(item => (
        <option key={item.value} value={item.value}>
          {item.extra.emoji} {item.label}
        </option>
      ))}
    </Select>
  );
}
```

### Vue.js

```vue
<template>
  <select v-model="selected">
    <option v-for="item in items" :key="item.value" :value="item.value">
      {{ item.label }}
    </option>
  </select>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const items = ref([]);
const selected = ref(null);

onMounted(async () => {
  const response = await fetch('/api/dictionaries/countries');
  const data = await response.json();
  items.value = data.data.items;
});
</script>
```

---

## Creating Custom Dictionaries

### 1. Create Dictionary Class

```php
<?php

namespace App\Dictionaries;

use Cartino\Dictionaries\BasicDictionary;

class ProductCategories extends BasicDictionary
{
    protected array $keywords = ['product', 'category'];

    protected function getItems(): array
    {
        return [
            ['value' => 'electronics', 'label' => 'Electronics', 'icon' => 'cpu'],
            ['value' => 'clothing', 'label' => 'Clothing', 'icon' => 'shirt'],
            ['value' => 'food', 'label' => 'Food & Beverage', 'icon' => 'coffee'],
        ];
    }
}
```

### 2. Register in Config

```php
// config/cartino.php

return [
    'dictionaries' => [
        'product_categories' => \App\Dictionaries\ProductCategories::class,
        'custom_statuses' => \App\Dictionaries\CustomStatuses::class,
    ],
];
```

### 3. Use via API

```bash
GET /api/dictionaries/product_categories
```

---

## Caching

All dictionary responses are cached for 1 hour (3600 seconds) to optimize performance:

```php
Cache::remember("dictionary:{$handle}:{$search}", 3600, function () {
    // Dictionary logic
});
```

To clear cache:
```php
Cache::forget("dictionary:countries:all");
Cache::flush(); // Clear all
```

---

## Address Autocomplete Integration

Complete example for address forms with country/state selection:

```javascript
async function AddressForm() {
  // Load countries
  const countries = await fetch('/api/dictionaries/countries');

  // When country changes, load states
  function onCountryChange(countryCode) {
    const country = countries.data.items.find(
      item => item.value === countryCode
    );

    const states = country?.extra?.states || [];

    return states.map(state => ({
      value: state.code,
      label: state.name
    }));
  }

  // Load phone prefix
  function onCountryPhonePrefix(countryCode) {
    const country = countries.data.items.find(
      item => item.value === countryCode
    );

    return country?.extra?.phone || '';
  }

  return (
    <form>
      <Select
        name="country"
        options={countries.data.items}
        onChange={(code) => {
          setStates(onCountryChange(code));
          setPhonePrefix(onCountryPhonePrefix(code));
        }}
      />

      {states.length > 0 && (
        <Select name="state" options={states} />
      )}

      <Input
        name="phone"
        prefix={phonePrefix}
      />
    </form>
  );
}
```

---

## Performance Tips

1. **Use caching**: Responses are cached, but store commonly used dictionaries in localStorage/sessionStorage
2. **Lazy load**: Load dictionaries only when needed
3. **Preload critical data**: Preload countries/currencies on app init
4. **Search server-side**: Use `?search=` parameter instead of client-side filtering for large dictionaries
5. **Batch requests**: Load multiple dictionaries in parallel with Promise.all()

```javascript
// Good: Parallel loading
const [countries, currencies, languages] = await Promise.all([
  fetch('/api/dictionaries/countries'),
  fetch('/api/dictionaries/currencies'),
  fetch('/api/dictionaries/languages')
]);

// Bad: Sequential loading
const countries = await fetch('/api/dictionaries/countries');
const currencies = await fetch('/api/dictionaries/currencies');
const languages = await fetch('/api/dictionaries/languages');
```

---

## Localization

All dictionaries support localization via Laravel's translation system:

```php
// In dictionary
['value' => 'pending', 'label' => __('cartino::dictionaries.order_pending')]

// resources/lang/it/dictionaries.php
return [
    'order_pending' => 'In attesa',
    'order_confirmed' => 'Confermato',
];

// resources/lang/en/dictionaries.php
return [
    'order_pending' => 'Pending',
    'order_confirmed' => 'Confirmed',
];
```

Dictionary responses automatically use the current application locale.

---

## Notes

- **Read-only**: Dictionaries are read-only reference data
- **No authentication**: All dictionary endpoints are public
- **Extensible**: Easy to add custom dictionaries via config
- **Type-safe**: Can be used with TypeScript for type safety
- **GraphQL ready**: Built-in GraphQL support (via Statamic traits)
- **Searchable**: All dictionaries support search functionality
- **Cached**: Automatic caching for performance
