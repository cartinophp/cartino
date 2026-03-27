# API Keys Configuration

## Test API Key (Development Only)
CARTINO_TEST_API_KEY=ck_test_public_key_1234567890abcdef

## Production API Keys
# Le chiavi di produzione devono essere generate tramite l'admin panel
# Endpoint: POST /api/admin/api-keys

## Uso delle API Keys

### 1. Header HTTP (Raccomandato)
```bash
curl -H "X-API-Key: ck_test_public_key_1234567890abcdef" \
     https://your-domain.com/api/products
```

### 2. Query Parameter (Alternativa)
```bash
curl https://your-domain.com/api/products?api_key=ck_test_public_key_1234567890abcdef
```

## Tipi di API Keys

### Full Access
- Accesso completo a tutte le API
- Usare solo per backend trusted

### Read Only
- Solo operazioni GET/HEAD/OPTIONS
- Ideale per analytics e reporting

### Custom
- Permessi granulari configurabili
- Es: ['view products', 'create orders', 'view customers']

## Gestione API Keys

### Creare una nuova key
```bash
curl -X POST https://your-domain.com/api/admin/api-keys \
  -H "Authorization: Bearer YOUR_AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Integration XYZ",
    "description": "Key for external integration",
    "type": "custom",
    "permissions": ["view products", "create orders"],
    "expires_at": "2025-12-31"
  }'
```

### Revocare una key
```bash
curl -X POST https://your-domain.com/api/admin/api-keys/{id}/revoke \
  -H "Authorization: Bearer YOUR_AUTH_TOKEN"
```

### Rigenerare una key
```bash
curl -X POST https://your-domain.com/api/admin/api-keys/{id}/regenerate \
  -H "Authorization: Bearer YOUR_AUTH_TOKEN"
```

## Security Best Practices

1. **Non committare le chiavi nel repository**
2. **Usare chiavi diverse per ogni ambiente**
3. **Impostare date di scadenza appropriate**
4. **Monitorare l'uso tramite last_used_at**
5. **Revocare immediatamente le chiavi compromesse**
6. **Usare permessi minimi necessari (principio del least privilege)**

## Middleware

Il middleware `ValidateApiKey` è già registrato e può essere applicato alle route:

```php
Route::middleware('api.key')->group(function () {
    // Route protette da API key
});
```

## Permessi Disponibili

I permessi seguono il pattern Spatie:
- `view {resource}s`
- `create {resource}s`
- `edit {resource}s`
- `delete {resource}s`

Esempi:
- `view products`
- `create orders`
- `edit customers`
- `delete entries`
