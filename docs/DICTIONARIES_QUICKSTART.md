# Dictionaries - Quick Start

## Endpoint Base

Tutti i dictionaries sono sotto `/api/data`:

```bash
GET /api/data/dictionaries                 # Lista tutti
GET /api/data/dictionaries/{handle}        # Ottieni dictionary
GET /api/data/dictionaries/{handle}/{key}  # Ottieni item
GET /api/data/dictionaries/search?q=...    # Ricerca
```

---

## Uso Base

### 1. Lista Countries

```bash
curl GET /api/data/dictionaries/countries
```

### 2. Address Autocomplete

```javascript
// Carica paesi
const response = await fetch('/api/data/dictionaries/countries');
const countries = response.data.items;

// Quando seleziona Italia, carica regioni
const italy = countries.find(c => c.value === 'IT');
const regions = italy.extra.states; // 20 regioni italiane

// Popola select
<select name="state">
  {regions.map(r => (
    <option value={r.code}>{r.name}</option>
  ))}
</select>
```

### 3. Multi-Currency

```javascript
const currencies = await fetch('/api/data/dictionaries/currencies');

currencies.data.items.map(curr => (
  <option value={curr.value}>
    {curr.extra.symbol} {curr.label}
  </option>
));

// Output: € Euro (EUR), $ US Dollar (USD), etc.
```

---

## Estendere Dictionary

Per aggiungere custom address types:

**File**: `config/cartino.php`

```php
'dictionary_extensions' => [
    'address_types' => [
        ['value' => 'warehouse', 'label' => 'Warehouse', 'icon' => 'building'],
        ['value' => 'office', 'label' => 'Office', 'icon' => 'briefcase'],
    ],
],
```

**Risultato**:

```bash
GET /api/data/dictionaries/address_types
```

```json
{
  "items": [
    {"value": "billing", "label": "Billing Address"},
    {"value": "shipping", "label": "Shipping Address"},
    {"value": "both", "label": "Billing & Shipping"},
    {"value": "warehouse", "label": "Warehouse"},     // ← Custom
    {"value": "office", "label": "Office"}            // ← Custom
  ],
  "is_extensible": true,
  "has_custom_items": true
}
```

---

## Dictionaries Disponibili

| Handle | Items | Estensibile |
|--------|-------|-------------|
| `countries` | 30+ | ❌ |
| `currencies` | 140+ | ❌ |
| `languages` | 15+ | ❌ |
| `locales` | 10+ | ❌ |
| `phone_prefixes` | 30+ | ❌ |
| `address_types` | 3 | ✅ |
| `payment_providers` | 8 | ✅ |
| `shipping_types` | 5 | ✅ |
| `order_statuses` | 7 | ✅ |
| `payment_statuses` | 6 | ✅ |
| `vat_rates` | 18 | ❌ |
| `units` | 13 | ✅ |

---

## Documentazione Completa

- **[DICTIONARIES.md](DICTIONARIES.md)** - Reference completa
- **[DICTIONARIES_EXTENSION.md](DICTIONARIES_EXTENSION.md)** - Guida estensione
