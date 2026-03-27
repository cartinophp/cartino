# Guida API Authentication

## Overview

Le REST API di Cartino supportano due modalità di autenticazione:

1. **Sanctum Token** - Per SPA e applicazioni web
2. **API Key** - Per integrazioni server-to-server

## Endpoint Pubblici

I seguenti endpoint sono accessibili senza autenticazione:

- `GET /api/products` - Lista prodotti
- `GET /api/categories` - Lista categorie
- `GET /api/brands` - Lista brand
- `GET /api/markets` - Informazioni mercati
- `GET /api/prices/*` - Prezzi pubblici
- `GET /api/countries` - Paesi
- `GET /api/currencies` - Valute
- `POST /api/cart/*` - Gestione carrello
- `POST /api/search` - Ricerca prodotti
- `POST /api/customers/register` - Registrazione clienti
- `POST /api/customers/login` - Login clienti

## Endpoint Protetti (Admin)

Tutti gli endpoint amministrativi richiedono autenticazione:

- `/api/permissions/*` - Gestione permessi
- `/api/roles/*` - Gestione ruoli
- `/api/users/*` - Gestione utenti
- `/api/customers/*` (admin) - Gestione clienti (admin)
- `/api/orders/*` (admin) - Gestione ordini (admin)
- `/api/fidelity/cards/*` - Gestione carte fedeltà
- `/api/api-keys/*` - Gestione API keys
- `/api/reports/*` - Report e analytics
- E molti altri endpoint di configurazione

## Come Usare l'API Key

### 1. Header HTTP (Raccomandato)

```bash
curl -H "X-API-Key: ck_your_api_key_here" \
  https://your-domain.com/api/permissions
```

### 2. Query Parameter

```bash
curl "https://your-domain.com/api/permissions?api_key=ck_your_api_key_here"
```

### Esempi con diversi client

#### JavaScript (Fetch)

```javascript
fetch('https://your-domain.com/api/permissions', {
  headers: {
    'X-API-Key': 'ck_your_api_key_here',
    'Accept': 'application/json'
  }
})
.then(response => response.json())
.then(data => console.log(data));
```

#### PHP (Guzzle)

```php
use GuzzleHttp\Client;

$client = new Client();
$response = $client->get('https://your-domain.com/api/permissions', [
    'headers' => [
        'X-API-Key' => 'ck_your_api_key_here',
        'Accept' => 'application/json',
    ]
]);

$data = json_decode($response->getBody(), true);
```

#### Python (Requests)

```python
import requests

headers = {
    'X-API-Key': 'ck_your_api_key_here',
    'Accept': 'application/json'
}

response = requests.get('https://your-domain.com/api/permissions', headers=headers)
data = response.json()
```

## Tipi di API Key

### 1. Full Access

```json
{
  "type": "full_access",
  "permissions": null
}
```

Accesso completo a tutti gli endpoint.

### 2. Read Only

```json
{
  "type": "read_only",
  "permissions": null
}
```

Solo operazioni GET/HEAD/OPTIONS consentite.

### 3. Custom

```json
{
  "type": "custom",
  "permissions": [
    "view_products",
    "edit_products",
    "view_orders"
  ]
}
```

Permessi granulari specifici.

## Creare una API Key

### Via Seeder (Sviluppo)

```bash
php artisan db:seed --class=ApiKeySeeder
```

Questo crea automaticamente:
- **Key di test**: per sviluppo locale
- **Key Analytics**: accesso read-only
- **Key Custom**: con permessi personalizzati

### Via API (Produzione)

```bash
POST /api/api-keys
Content-Type: application/json
Authorization: Bearer {sanctum_token}

{
  "name": "Integration API Key",
  "type": "full_access",
  "description": "Chiave per integrazione sistema esterno",
  "expires_at": "2026-12-31"
}
```

**Risposta:**

```json
{
  "data": {
    "id": 1,
    "name": "Integration API Key",
    "type": "full_access",
    "plain_key": "ck_abc123xyz...",
    "created_at": "2025-12-19T10:00:00Z"
  },
  "message": "API key creata con successo. ATTENZIONE: Salva questa chiave, non sarà più visibile!"
}
```

⚠️ **IMPORTANTE**: Il `plain_key` viene mostrato **solo una volta** alla creazione. Salvalo in un posto sicuro!

## Gestione API Keys

### Lista tutte le keys

```bash
GET /api/api-keys
```

### Visualizza dettagli key

```bash
GET /api/api-keys/{id}
```

### Revocare una key

```bash
POST /api/api-keys/{id}/revoke
```

### Attivare una key

```bash
POST /api/api-keys/{id}/activate
```

### Rigenerare una key

```bash
POST /api/api-keys/{id}/regenerate
```

Genera una nuova chiave mantenendo le stesse configurazioni.

### Eliminare una key

```bash
DELETE /api/api-keys/{id}
```

## Scadenza e Sicurezza

- Le API key possono avere una data di scadenza (`expires_at`)
- Le key vengono hashate con SHA-256 prima di essere salvate
- Ogni utilizzo aggiorna il campo `last_used_at`
- Le key possono essere temporaneamente disattivate (`is_active`)

## Error Handling

### 401 Unauthorized - Nessuna autenticazione

```json
{
  "message": "Autenticazione richiesta. Usa Sanctum token o API key (header X-API-Key o query parameter api_key)"
}
```

### 401 Unauthorized - Key non valida

```json
{
  "message": "API key non valida o scaduta"
}
```

### 403 Forbidden - Permessi insufficienti

```json
{
  "message": "Accesso negato per questo endpoint con l'API key fornita"
}
```

## Best Practices

1. **Non condividere mai le API keys** - Trattale come password
2. **Usa HTTPS** - Mai inviare keys su connessioni non sicure
3. **Rotazione regolare** - Rigenera le keys periodicamente
4. **Permessi minimi** - Assegna solo i permessi necessari
5. **Monitora l'uso** - Controlla `last_used_at` per rilevare anomalie
6. **Scadenza** - Imposta sempre una data di scadenza
7. **Revoca immediata** - In caso di compromissione, revoca subito la key

## Ambiente di Sviluppo

Per sviluppo locale, puoi usare la key demo creata dal seeder:

```bash
php artisan db:seed --class=ApiKeySeeder
```

La chiave verrà stampata nel terminale:

```
✓ API Key di test creata: ck_abc123...
  Aggiungi al tuo .env: CARTINO_TEST_API_KEY=ck_abc123...
```

Poi nel tuo `.env`:

```env
CARTINO_TEST_API_KEY=ck_abc123xyz...
```

## Migrazione da versione precedente

Se hai endpoint che prima non richiedevano autenticazione e ora sono protetti, hai due opzioni:

1. **Implementa autenticazione** - Aggiungi l'API key o Sanctum token
2. **Richiedi endpoint pubblici** - Se necessario, possiamo rendere pubblici alcuni endpoint specifici

## Supporto

Per problemi o domande:
- Verifica che l'API key sia attiva e non scaduta
- Controlla che l'header `X-API-Key` sia impostato correttamente
- Verifica i permessi della key con `GET /api/api-keys/{id}`
