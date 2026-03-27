# Test API Keys System

## Setup

1. Esegui le migrations:
```bash
php artisan migrate
```

2. Esegui il seeder per creare le API keys di test:
```bash
php artisan db:seed --class=ApiKeySeeder
```

3. Output del seeder mostrerà le chiavi generate:
```
✓ API Key di test creata: ck_test_public_key_1234567890abcdef
  Aggiungi al tuo .env: CARTINO_TEST_API_KEY=ck_test_public_key_1234567890abcdef

✓ API Key Analytics creata: ck_xxx...
✓ API Key Custom creata: ck_yyy...
```

## Test Esempi

### 1. Test con chiave Full Access
```bash
# Testare endpoint pubblico
curl -H "X-API-Key: ck_test_public_key_1234567890abcdef" \
     http://localhost:8000/api/products

# Testare endpoint admin (richiede anche autenticazione)
curl -H "X-API-Key: ck_test_public_key_1234567890abcdef" \
     -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
     http://localhost:8000/api/admin/products
```

### 2. Gestione API Keys (Super Admin)

#### Creare nuova API key
```bash
curl -X POST http://localhost:8000/api/admin/api-keys \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "My Integration",
    "description": "Integration for external service",
    "type": "full_access",
    "expires_at": "2025-12-31T23:59:59Z"
  }'
```

Risposta:
```json
{
  "success": true,
  "message": "API key creata con successo. ATTENZIONE: Salva questa chiave, non sarà più visibile!",
  "data": {
    "id": 4,
    "name": "My Integration",
    "key": "ck_a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6",  // ⚠️ Mostrata solo alla creazione!
    "type": "full_access",
    "is_active": true,
    ...
  }
}
```

#### Listar tutte le keys
```bash
curl http://localhost:8000/api/admin/api-keys \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Revocare una key
```bash
curl -X POST http://localhost:8000/api/admin/api-keys/4/revoke \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Rigenerare una key (ottieni nuova chiave)
```bash
curl -X POST http://localhost:8000/api/admin/api-keys/4/regenerate \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 3. Test con chiave Read-Only

```bash
# ✅ Funziona - operazione GET
curl -H "X-API-Key: YOUR_READONLY_KEY" \
     http://localhost:8000/api/products

# ❌ Fallisce - operazione POST
curl -X POST http://localhost:8000/api/admin/products \
     -H "X-API-Key: YOUR_READONLY_KEY" \
     -H "Content-Type: application/json" \
     -d '{"name": "Test"}'
```

### 4. Test con chiave Custom

API key con permessi: `['view products', 'create orders']`

```bash
# ✅ Funziona - ha permesso 'view products'
curl -H "X-API-Key: YOUR_CUSTOM_KEY" \
     http://localhost:8000/api/products

# ✅ Funziona - ha permesso 'create orders'
curl -X POST http://localhost:8000/api/orders \
     -H "X-API-Key: YOUR_CUSTOM_KEY" \
     -H "Content-Type: application/json" \
     -d '{...}'

# ❌ Fallisce - non ha permesso 'create products'
curl -X POST http://localhost:8000/api/admin/products \
     -H "X-API-Key: YOUR_CUSTOM_KEY" \
     -d '{...}'
```

## Monitoring

### Controllare last_used_at
```bash
curl http://localhost:8000/api/admin/api-keys \
  -H "Authorization: Bearer YOUR_TOKEN" \
  | jq '.data[] | {name, last_used_at}'
```

### Trovare keys scadute
```sql
SELECT * FROM api_keys 
WHERE expires_at < NOW() 
AND is_active = 1;
```

## Errori Comuni

### 401 - API key mancante
```json
{
  "message": "API key mancante. Usa header X-API-Key o query parameter api_key"
}
```
**Soluzione**: Aggiungi header `X-API-Key: your_key`

### 401 - API key non valida
```json
{
  "message": "API key non valida o scaduta"
}
```
**Soluzione**: Verifica che la key sia corretta e non scaduta

### 403 - Accesso negato
```json
{
  "message": "Accesso negato per questo endpoint"
}
```
**Soluzione**: La key non ha i permessi necessari per questo endpoint

## Integrazione con Postman

1. Crea una collection
2. In Authorization, seleziona "API Key"
3. Key: `X-API-Key`
4. Value: `ck_test_public_key_1234567890abcdef`
5. Add to: `Header`

## Integrazione con Frontend (JavaScript)

```javascript
const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'X-API-Key': process.env.VUE_APP_API_KEY
  }
});

// Fetch products
const products = await api.get('/products');
```

## Note di Sicurezza

⚠️ **IMPORTANTE**:
- La chiave di test `ck_test_public_key_1234567890abcdef` è per SVILUPPO ONLY
- In produzione, genera sempre nuove chiavi tramite l'admin panel
- Non esporre le chiavi nel frontend code
- Usa variabili d'ambiente per le chiavi
- Monitora `last_used_at` per individuare chiavi non utilizzate
- Imposta `expires_at` appropriati
