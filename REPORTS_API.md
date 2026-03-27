# Reports API - Analytics & Business Intelligence

Sistema di reportistica completo per analytics e business intelligence, simile a Shopify Analytics.

## Endpoints Disponibili

### 1. Dashboard Summary
**GET** `/api/admin/reports/dashboard`

Riepilogo generale con metriche chiave del business.

**Parametri:**
- `from` (optional): Data inizio periodo (YYYY-MM-DD)
- `to` (optional): Data fine periodo (YYYY-MM-DD)

**Risposta:**
```json
{
  "success": true,
  "data": {
    "period": {
      "from": "2024-11-12",
      "to": "2024-12-12"
    },
    "sales": {
      "total_revenue": 125000.50,
      "total_orders": 543,
      "average_order_value": 230.24,
      "total_items_sold": 1234
    },
    "customers": {
      "new_customers": 89,
      "total_customers": 1542,
      "returning_customers": 234
    },
    "products": {
      "total_products": 450,
      "low_stock_products": 23,
      "out_of_stock_products": 5
    }
  }
}
```

### 2. Sales Report
**GET** `/api/admin/reports/sales`

Report vendite con time series dettagliato.

**Parametri:**
- `from` (optional): Data inizio
- `to` (optional): Data fine
- `group_by` (optional): `hour`, `day`, `week`, `month`, `year` (default: `day`)

**Esempio:**
```bash
GET /api/admin/reports/sales?from=2024-12-01&to=2024-12-12&group_by=day
```

**Risposta:**
```json
{
  "success": true,
  "data": {
    "period": {
      "from": "2024-12-01",
      "to": "2024-12-12",
      "group_by": "day"
    },
    "summary": {
      "total_revenue": 125000.50,
      "total_orders": 543,
      "average_order_value": 230.24,
      "total_items_sold": 1234
    },
    "data": [
      {
        "period": "2024-12-01",
        "orders_count": 45,
        "revenue": 10234.50,
        "average_order_value": 227.43,
        "items_sold": 98
      },
      {
        "period": "2024-12-02",
        "orders_count": 52,
        "revenue": 12456.00,
        "average_order_value": 239.54,
        "items_sold": 112
      }
    ]
  }
}
```

### 3. Customers Report
**GET** `/api/admin/reports/customers`

Analytics sui clienti: acquisizione, retention, top spenders.

**Parametri:**
- `from` (optional): Data inizio
- `to` (optional): Data fine
- `group_by` (optional): `day`, `week`, `month`, `year`

**Risposta:**
```json
{
  "success": true,
  "data": {
    "period": { "from": "2024-11-12", "to": "2024-12-12" },
    "summary": {
      "new_customers": 89,
      "total_customers": 1542,
      "average_ltv": 450.25
    },
    "new_customers_timeline": [
      { "period": "2024-12-01", "count": 8 },
      { "period": "2024-12-02", "count": 12 }
    ],
    "top_customers": [
      {
        "id": 123,
        "name": "Mario Rossi",
        "email": "mario@example.com",
        "orders_count": 15,
        "total_spent": 5234.50
      }
    ]
  }
}
```

### 4. Products Performance
**GET** `/api/admin/reports/products`

Report performance prodotti: più venduti, revenue generata.

**Parametri:**
- `from` (optional): Data inizio
- `to` (optional): Data fine
- `limit` (optional): Numero prodotti da mostrare (default: 20, max: 100)

**Risposta:**
```json
{
  "success": true,
  "data": {
    "period": { "from": "2024-11-12", "to": "2024-12-12" },
    "top_selling": [
      {
        "id": 45,
        "name": "Product A",
        "sku": "PROD-001",
        "units_sold": 234,
        "revenue": 12345.00,
        "current_stock": 45
      }
    ],
    "top_revenue": [
      {
        "id": 67,
        "name": "Product B",
        "sku": "PROD-002",
        "units_sold": 89,
        "revenue": 15678.00,
        "average_price": 176.15
      }
    ]
  }
}
```

### 5. Revenue Report
**GET** `/api/admin/reports/revenue`

Report finanziario dettagliato con gross/net sales, tasse, sconti.

**Parametri:**
- `from` (optional): Data inizio
- `to` (optional): Data fine
- `group_by` (optional): `day`, `week`, `month`, `year`

**Risposta:**
```json
{
  "success": true,
  "data": {
    "period": { "from": "2024-12-01", "to": "2024-12-12", "group_by": "day" },
    "summary": {
      "gross_sales": 130000.00,
      "discounts": 5000.00,
      "taxes": 2500.00,
      "shipping": 1200.00,
      "net_sales": 128700.00,
      "orders_count": 543
    },
    "timeline": [
      {
        "period": "2024-12-01",
        "gross_sales": 11000.00,
        "discounts": 400.00,
        "taxes": 220.00,
        "shipping": 100.00,
        "net_sales": 10920.00,
        "orders_count": 45
      }
    ]
  }
}
```

### 6. Inventory Report
**GET** `/api/admin/reports/inventory`

Report giacenze: stock disponibile, low stock, out of stock.

**Parametri:**
- `low_stock_threshold` (optional): Soglia per low stock (default: 10)

**Risposta:**
```json
{
  "success": true,
  "data": {
    "summary": {
      "total_products": 450,
      "in_stock": 422,
      "low_stock": 23,
      "out_of_stock": 5,
      "total_inventory_value": 345678.90
    },
    "low_stock_products": [
      {
        "id": 123,
        "name": "Product Low",
        "sku": "PROD-LOW-001",
        "stock_quantity": 5,
        "price_amount": 49.99
      }
    ],
    "out_of_stock_products": [
      {
        "id": 456,
        "name": "Product Out",
        "sku": "PROD-OUT-001",
        "price_amount": 99.99
      }
    ]
  }
}
```

### 7. Orders by Status
**GET** `/api/admin/reports/orders-by-status`

Distribuzione ordini per stato.

**Parametri:**
- `from` (optional): Data inizio
- `to` (optional): Data fine

**Risposta:**
```json
{
  "success": true,
  "data": {
    "period": { "from": "2024-12-01", "to": "2024-12-12" },
    "by_status": [
      { "status": "pending", "count": 45, "total_amount": 12345.00 },
      { "status": "processing", "count": 123, "total_amount": 34567.00 },
      { "status": "completed", "count": 345, "total_amount": 78901.00 },
      { "status": "cancelled", "count": 12, "total_amount": 2345.00 }
    ],
    "total_orders": 525,
    "total_amount": 128158.00
  }
}
```

### 8. Export Report
**GET** `/api/admin/reports/export`

Esporta dati grezzi per ulteriore analisi (CSV/Excel ready).

**Parametri:**
- `report_type` (required): `sales`, `customers`, `products`, `revenue`, `orders`
- `from` (optional): Data inizio
- `to` (optional): Data fine
- `format` (optional): `json`, `csv` (default: `json`)

**Esempio:**
```bash
GET /api/admin/reports/export?report_type=sales&from=2024-12-01&to=2024-12-12
```

**Risposta:**
```json
{
  "success": true,
  "data": {
    "report_type": "sales",
    "period": { "from": "2024-12-01", "to": "2024-12-12" },
    "records_count": 543,
    "data": [
      {
        "id": 1234,
        "order_number": "ORD-2024-001",
        "customer_id": 123,
        "status": "completed",
        "total_amount": 234.50,
        "created_at": "2024-12-01T10:30:00Z"
      }
    ]
  }
}
```

## Autenticazione

Tutti gli endpoint richiedono autenticazione admin:

```bash
# Con Sanctum token
curl -H "Authorization: Bearer YOUR_TOKEN" \
     https://domain.com/api/admin/reports/dashboard

# Con API Key
curl -H "X-API-Key: YOUR_API_KEY" \
     https://domain.com/api/admin/reports/dashboard
```

## Use Cases

### Dashboard Real-time
```bash
# Dashboard ultimi 30 giorni
GET /api/admin/reports/dashboard

# Dashboard personalizzato
GET /api/admin/reports/dashboard?from=2024-01-01&to=2024-12-31
```

### Analisi Vendite Settimanali
```bash
GET /api/admin/reports/sales?from=2024-12-01&to=2024-12-07&group_by=day
```

### Top 50 Prodotti del Mese
```bash
GET /api/admin/reports/products?from=2024-12-01&to=2024-12-31&limit=50
```

### Export per Business Intelligence
```bash
# Export ordini completi
GET /api/admin/reports/export?report_type=orders&from=2024-01-01&to=2024-12-31

# Processare con strumenti esterni (Power BI, Tableau, etc.)
```

### Monitoraggio Inventario
```bash
# Prodotti sotto soglia 20 unità
GET /api/admin/reports/inventory?low_stock_threshold=20
```

### Report Finanziari Mensili
```bash
GET /api/admin/reports/revenue?from=2024-12-01&to=2024-12-31&group_by=month
```

## Integrazione con Software Terzi

### Python Example
```python
import requests

headers = {
    'X-API-Key': 'ck_your_api_key',
    'Accept': 'application/json'
}

# Fetch sales report
response = requests.get(
    'https://your-domain.com/api/admin/reports/sales',
    headers=headers,
    params={
        'from': '2024-12-01',
        'to': '2024-12-31',
        'group_by': 'day'
    }
)

data = response.json()
print(f"Total revenue: {data['data']['summary']['total_revenue']}")
```

### JavaScript Example
```javascript
const apiKey = 'ck_your_api_key';

async function getDashboard() {
  const response = await fetch('https://your-domain.com/api/admin/reports/dashboard', {
    headers: {
      'X-API-Key': apiKey,
      'Accept': 'application/json'
    }
  });
  
  const data = await response.json();
  return data.data;
}
```

## Permessi Richiesti

Per accedere ai report, l'utente o l'API key deve avere questi permessi:
- `view reports` - Accesso base a tutti i report
- Oppure essere super admin (`is_super = true`)

## Rate Limiting

I report possono essere costosi computazionalmente:
- Max 60 richieste/minuto per utente
- Report con periodi > 1 anno potrebbero essere limitati
- Usa caching quando possibile

## Best Practices

1. **Usa date range appropriati**: Evita periodi troppo lunghi per report dettagliati
2. **Cachare i risultati**: I dati storici cambiano raramente
3. **Scheduling**: Esegui export pesanti in orari di basso traffico
4. **Aggregazione**: Usa `group_by=month` per periodi lunghi invece di `day`
5. **Filtri**: Usa parametri per limitare i dati necessari
