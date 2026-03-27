# API Headers - Multi-Market Support

CartinoPHP supporta headers HTTP per gestire il contesto multi-market nelle API REST.

---

## 📋 Headers Supportati

### Market Context

| Header | Type | Example | Description |
|--------|------|---------|-------------|
| `X-Market` | string | `IT-B2C` | Market code |
| `X-Market-ID` | integer | `1` | Market ID |
| `X-Site` | string | `it-shop` | Site handle |
| `X-Site-ID` | integer | `2` | Site ID |
| `X-Channel` | string | `web` | Channel slug |
| `X-Channel-ID` | integer | `3` | Channel ID |
| `X-Catalog` | string | `retail` | Catalog slug |
| `X-Catalog-ID` | integer | `1` | Catalog ID |

### Localization

| Header | Type | Example | Description |
|--------|------|---------|-------------|
| `X-Currency` | string | `EUR` | Currency code (ISO 4217) |
| `X-Locale` | string | `it_IT` | Locale code |
| `X-Country` | string | `IT` | Country code (ISO 3166-1 alpha-2) |
| `Accept-Language` | string | `it-IT,it;q=0.9` | Standard browser language header |
| `Accept-Currency` | string | `EUR` | Preferred currency (custom) |

---

## 🔧 Usage Examples

### Example 1: Get Product Price for Italian Market

```bash
curl -X GET https://api.shop.it/api/v1/prices/show \
  -H "X-Market: IT-B2C" \
  -H "X-Currency: EUR" \
  -H "X-Locale: it_IT" \
  -H "Content-Type: application/json" \
  -d '{
    "variant_id": 123,
    "quantity": 5
  }'
```

**Response:**
```json
{
  "data": {
    "variant_id": 123,
    "sku": "TSHIRT-BLK-M",
    "price": {
      "id": 456,
      "amount": 2125,
      "formatted_amount": "21.25",
      "currency": "EUR",
      "tax": {
        "included": true,
        "rate": 22.0
      }
    }
  }
}
```

---

### Example 2: Get Store Configuration

```bash
curl -X GET https://api.shop.com/api/v1/store \
  -H "X-Market: US-B2C" \
  -H "X-Currency: USD" \
  -H "Accept-Language: en-US"
```

**Response:**
```json
{
  "data": {
    "context": {
      "market": {
        "id": 2,
        "code": "US-B2C",
        "name": "United States B2C"
      },
      "currency": "USD",
      "locale": "en_US"
    },
    "available": {
      "markets": [...],
      "locales": [...],
      "currencies": [...]
    }
  }
}
```

---

### Example 3: Update Store Context

```bash
curl -X POST https://api.shop.eu/api/v1/store \
  -H "Content-Type: application/json" \
  -d '{
    "market_code": "EU-B2C",
    "currency": "EUR",
    "locale": "fr_FR",
    "country_code": "FR"
  }'
```

**Response:**
```json
{
  "message": "Store context updated successfully",
  "data": {
    "context": {
      "market": {
        "code": "EU-B2C",
        "name": "Europe B2C"
      },
      "currency": "EUR",
      "locale": "fr_FR",
      "country_code": "FR"
    }
  }
}
```

---

### Example 4: Get Available Markets

```bash
curl -X GET https://api.shop.com/api/v1/markets
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "code": "IT-B2C",
      "name": "Italia B2C",
      "type": "b2c",
      "currencies": {
        "default": "EUR",
        "supported": ["EUR"]
      },
      "locales": {
        "default": "it_IT",
        "supported": ["it_IT", "en_US"]
      },
      "status": "active"
    },
    ...
  ]
}
```

---

### Example 5: Calculate Tax for Market

```bash
curl -X POST https://api.shop.it/api/v1/markets/IT-B2C/calculate-tax \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 10000,
    "country_code": "IT",
    "product_type": "physical"
  }'
```

**Response:**
```json
{
  "data": {
    "tax_amount": 2200,
    "amount_without_tax": 10000,
    "amount_with_tax": 12200,
    "effective_tax_rate": 22.0,
    "applied_rates": [
      {
        "name": "VAT Italy",
        "rate": 22.0,
        "type": "percentage"
      }
    ]
  }
}
```

---

## 🔄 Header Priority

Quando multiple headers sono presenti, CartinoPHP usa questa priorità:

### Market Resolution
1. `X-Market-ID` (highest priority)
2. `X-Market` (market code)
3. Session/Cookie
4. Default market

### Currency Resolution
1. `X-Currency`
2. `Accept-Currency`
3. Market default currency
4. Session/Cookie
5. Config default

### Locale Resolution
1. `X-Locale`
2. `Accept-Language` (parsed)
3. Market default locale
4. User preference (if authenticated)
5. Session/Cookie
6. Config default

---

## 🛡️ Validation

Headers sono validati automaticamente dal middleware `AcceptMarketHeaders`:

- **Market**: Deve esistere ed essere attivo
- **Currency**: Deve essere supportato dal market (3-letter ISO code)
- **Locale**: Deve essere supportato dal market
- **Country**: 2-letter ISO code

Se un header non è valido, viene **ignorato** senza errore e si usa il fallback.

---

## 📦 Middleware Setup

### routes/api.php

```php
use Cartino\Http\Middleware\AcceptMarketHeaders;

Route::prefix('api/v1')->middleware(['api', AcceptMarketHeaders::class])->group(function () {
    // Store endpoints
    Route::get('/store', [StoreController::class, 'index']);
    Route::post('/store', [StoreController::class, 'store']);
    Route::post('/store/reset', [StoreController::class, 'reset']);

    // Market endpoints
    Route::get('/markets', [MarketController::class, 'index']);
    Route::get('/markets/current', [MarketController::class, 'current']);
    Route::get('/markets/{market}', [MarketController::class, 'show']);
    Route::post('/markets/set-context', [MarketController::class, 'setContext']);
    Route::post('/markets/switch', [MarketController::class, 'switch']);
    Route::get('/markets/{market}/configuration', [MarketController::class, 'configuration']);
    Route::get('/markets/{market}/payment-methods', [MarketController::class, 'paymentMethods']);
    Route::get('/markets/{market}/shipping-methods', [MarketController::class, 'shippingMethods']);
    Route::post('/markets/{market}/calculate-tax', [MarketController::class, 'calculateTax']);

    // Price endpoints
    Route::get('/prices/show', [PriceController::class, 'show']);
    Route::post('/prices/bulk', [PriceController::class, 'bulk']);
    Route::get('/prices/tiers', [PriceController::class, 'tiers']);
    Route::post('/prices/calculate', [PriceController::class, 'calculate']);
});
```

### Kernel.php (Laravel 11+)

```php
use Cartino\Http\Middleware\AcceptMarketHeaders;

protected $middlewareGroups = [
    'api' => [
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        AcceptMarketHeaders::class, // Add this
    ],
];
```

---

## 🌐 CORS Configuration

Se usi CORS, assicurati di permettere gli headers custom:

### config/cors.php

```php
return [
    'allowed_headers' => [
        '*',
        'X-Market',
        'X-Market-ID',
        'X-Site',
        'X-Site-ID',
        'X-Channel',
        'X-Channel-ID',
        'X-Catalog',
        'X-Catalog-ID',
        'X-Currency',
        'X-Locale',
        'X-Country',
        'Accept-Currency',
    ],
    'exposed_headers' => [
        'X-Market',
        'X-Currency',
        'X-Locale',
    ],
];
```

---

## 🚀 Client Libraries

### JavaScript/TypeScript

```typescript
import axios from 'axios';

const api = axios.create({
  baseURL: 'https://api.shop.it/api/v1',
  headers: {
    'X-Market': 'IT-B2C',
    'X-Currency': 'EUR',
    'X-Locale': 'it_IT',
    'Content-Type': 'application/json',
  },
});

// Get price
const response = await api.get('/prices/show', {
  params: {
    variant_id: 123,
    quantity: 5,
  },
});

// Update store context
await api.post('/store', {
  market_code: 'EU-B2C',
  currency: 'EUR',
  locale: 'fr_FR',
});
```

### PHP (Guzzle)

```php
use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'https://api.shop.it/api/v1',
    'headers' => [
        'X-Market' => 'IT-B2C',
        'X-Currency' => 'EUR',
        'X-Locale' => 'it_IT',
        'Content-Type' => 'application/json',
    ],
]);

// Get price
$response = $client->get('/prices/show', [
    'json' => [
        'variant_id' => 123,
        'quantity' => 5,
    ],
]);

$data = json_decode($response->getBody(), true);
```

### Python (requests)

```python
import requests

headers = {
    'X-Market': 'IT-B2C',
    'X-Currency': 'EUR',
    'X-Locale': 'it_IT',
    'Content-Type': 'application/json',
}

# Get price
response = requests.get(
    'https://api.shop.it/api/v1/prices/show',
    headers=headers,
    json={'variant_id': 123, 'quantity': 5}
)

data = response.json()
```

---

## 📊 Response Headers

L'API può restituire headers informativi nella response:

| Header | Example | Description |
|--------|---------|-------------|
| `X-Market` | `IT-B2C` | Active market code |
| `X-Currency` | `EUR` | Active currency |
| `X-Locale` | `it_IT` | Active locale |
| `X-RateLimit-Limit` | `60` | Rate limit |
| `X-RateLimit-Remaining` | `59` | Remaining requests |

---

## 🔒 Security Best Practices

1. **Always validate market access** - Check user permissions for market
2. **Rate limiting** - Implement per-market rate limits
3. **Sanitize headers** - Headers are validated but always sanitize
4. **HTTPS only** - Never use HTTP for market context
5. **Session binding** - Bind market context to session for security
6. **Audit logging** - Log market switches for security

---

## 🐛 Troubleshooting

### Issue: Headers not working

**Check:**
1. Middleware is registered in `api` group
2. CORS configuration allows custom headers
3. Headers are spelled correctly (case-sensitive)
4. Market/Currency/Locale are valid and supported

### Issue: Market not switching

**Debug:**
```bash
curl -v -X GET https://api.shop.it/api/v1/markets/current \
  -H "X-Market: IT-B2C"
```

Check response headers and session cookies.

### Issue: Currency validation failing

**Ensure:**
- Currency is 3-letter ISO code (uppercase)
- Currency is supported by the market
- Market exists and is active

---

## 📚 Related Documentation

- [Multi-Market Architecture](./MULTI_MARKET_ARCHITECTURE.md)
- [API Reference](./API_REFERENCE.md)
- [PricingContext DTO](./PRICING_CONTEXT.md)
