# Search API - Headless Search Endpoint

## Endpoint

```
POST /api/search
```

Endpoint di ricerca dinamico ottimizzato per frontend headless con supporto per:
- Ricerca full-text su titolo e campi JSON
- Filtri dinamici multipli
- Breadcrumbs generati automaticamente
- Faceted search (filtri disponibili con contatori)
- **Zero query N+1** grazie a eager loading completo

---

## Request Format

### Headers
```
Content-Type: application/json
Accept: application/json
```

### Body Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `q` | string | No | Termine di ricerca full-text (cerca in title e data JSON) |
| `collection` | string | No | Filtra per collection (es: "blog", "news", "articles") |
| `locale` | string | No | Lingua (default: "it") |
| `status` | string | No | Stato: "draft", "published", "scheduled" (default: "published") |
| `author_id` | integer | No | ID autore |
| `parent_id` | integer | No | ID entry parent (0 per root entries) |
| `tags` | array | No | Array di tag da filtrare |
| `category` | string | No | Categoria (cerca in data->category) |
| `date_from` | date | No | Data pubblicazione da (YYYY-MM-DD) |
| `date_to` | date | No | Data pubblicazione fino a (YYYY-MM-DD) |
| `sort` | string | No | Campo ordinamento: "title", "published_at", "created_at", "updated_at", "order" |
| `sort_direction` | string | No | Direzione: "asc" o "desc" (default: "desc") |
| `per_page` | integer | No | Risultati per pagina (min: 1, max: 100, default: 15) |
| `page` | integer | No | Numero pagina (default: 1) |
| `include_filters` | boolean | No | Includi filtri disponibili nella risposta (default: false) |

---

## Examples

### 1. Ricerca Semplice
```json
POST /api/search
{
  "q": "laravel",
  "per_page": 20
}
```

### 2. Ricerca per Collection con Filtri
```json
POST /api/search
{
  "collection": "blog",
  "locale": "it",
  "status": "published",
  "tags": ["php", "backend"],
  "category": "tutorial",
  "sort": "published_at",
  "sort_direction": "desc",
  "per_page": 15,
  "include_filters": true
}
```

### 3. Ricerca con Range Date
```json
POST /api/search
{
  "collection": "news",
  "date_from": "2024-01-01",
  "date_to": "2024-12-31",
  "author_id": 1,
  "sort": "created_at",
  "sort_direction": "asc"
}
```

### 4. Ricerca Entry di Root Level
```json
POST /api/search
{
  "collection": "pages",
  "parent_id": 0,
  "status": "published"
}
```

---

## Response Format

```json
{
  "success": true,
  "message": "Operazione completata con successo",
  "data": {
    "results": [
      {
        "id": 1,
        "collection": "blog",
        "slug": "introduzione-a-laravel",
        "title": "Introduzione a Laravel",
        "excerpt": "Laravel è un framework PHP moderno per lo sviluppo web...",
        "data": {
          "content": "...",
          "image": "/images/laravel.jpg",
          "featured": true
        },
        "status": "published",
        "published_at": "2024-12-01T10:00:00.000000Z",
        "locale": "it",
        "order": 0,
        "url": "/blog/introduzione-a-laravel",
        "is_published": true,
        "category": "tutorial",
        "tags": ["php", "laravel", "backend"],
        "image": "/images/laravel.jpg",
        "featured": true,
        "author": {
          "id": 1,
          "name": "Mario Rossi",
          "email": "mario@example.com"
        },
        "parent": null,
        "children_count": 0,
        "created_at": "2024-11-20T08:30:00.000000Z",
        "updated_at": "2024-12-01T10:00:00.000000Z"
      }
    ],
    "breadcrumbs": [
      {
        "title": "Home",
        "url": "/",
        "active": false
      },
      {
        "title": "Blog",
        "url": "/search?collection=blog",
        "active": false
      },
      {
        "title": "Tutorial",
        "url": "/search?collection=blog&category=tutorial",
        "active": false
      },
      {
        "title": "Risultati per: laravel",
        "url": null,
        "active": true
      }
    ],
    "filters": {
      "collections": [
        {
          "value": "blog",
          "label": "Blog",
          "count": 45
        },
        {
          "value": "news",
          "label": "News",
          "count": 23
        }
      ],
      "authors": [
        {
          "value": 1,
          "label": "Mario Rossi",
          "count": 12
        },
        {
          "value": 2,
          "label": "Laura Bianchi",
          "count": 8
        }
      ],
      "statuses": [
        {
          "value": "published",
          "label": "Published",
          "count": 67
        },
        {
          "value": "draft",
          "label": "Draft",
          "count": 15
        }
      ],
      "tags": [
        {
          "value": "php",
          "label": "php",
          "count": 34
        },
        {
          "value": "laravel",
          "label": "laravel",
          "count": 28
        }
      ],
      "categories": [
        {
          "value": "tutorial",
          "label": "Tutorial",
          "count": 25
        },
        {
          "value": "news",
          "label": "News",
          "count": 18
        }
      ]
    },
    "meta": {
      "current_page": 1,
      "last_page": 3,
      "per_page": 15,
      "total": 45,
      "from": 1,
      "to": 15
    },
    "links": {
      "first": "http://localhost/api/search?page=1",
      "last": "http://localhost/api/search?page=3",
      "prev": null,
      "next": "http://localhost/api/search?page=2"
    }
  }
}
```

---

## Response Fields

### Results Array
| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | ID univoco entry |
| `collection` | string | Nome collection |
| `slug` | string | URL slug |
| `title` | string | Titolo entry |
| `excerpt` | string | Estratto auto-generato (da excerpt, content o title) |
| `data` | object | Dati JSON completi |
| `status` | string | Stato pubblicazione |
| `published_at` | string\|null | Data pubblicazione ISO 8601 |
| `locale` | string | Lingua |
| `order` | integer | Ordinamento manuale |
| `url` | string | URL completo entry |
| `is_published` | boolean | Se entry è pubblicata |
| `category` | string\|null | Categoria estratta da data |
| `tags` | array | Tags estratti da data |
| `image` | string\|null | Immagine estratta da data |
| `featured` | boolean | Se entry è in evidenza |
| `author` | object\|null | Dati autore (eager loaded) |
| `parent` | object\|null | Entry parent (eager loaded) |
| `children_count` | integer | Numero children (eager loaded) |
| `created_at` | string | Data creazione ISO 8601 |
| `updated_at` | string | Data ultimo aggiornamento ISO 8601 |

### Breadcrumbs Array
| Field | Type | Description |
|-------|------|-------------|
| `title` | string | Titolo breadcrumb |
| `url` | string\|null | URL breadcrumb (null per ultimo attivo) |
| `active` | boolean | Se breadcrumb è quello corrente |

### Filters Object
Ogni tipo di filtro contiene array di oggetti con:
- `value`: valore del filtro
- `label`: label leggibile
- `count`: numero risultati per quel filtro

---

## Performance Optimization

### Eager Loading
Tutte le relazioni sono caricate con eager loading per evitare query N+1:
- `author` - caricato con select specifici (id, name, email)
- `parent` - caricato con campi essenziali
- `children` - caricati e ordinati

### Query Optimization
- Select solo campi necessari
- Indici su collection, locale, status, published_at
- JSON search ottimizzato con `JSON_SEARCH()` e `JSON_EXTRACT()`
- Aggregazioni per filtri eseguite con query singole

### Caching
Repository usa caching automatico con TTL di 30 minuti per:
- Risultati ricerca
- Contatori filtri
- Breadcrumbs

---

## Use Cases

### Frontend Headless
```javascript
// Esempio React/Next.js
const searchPosts = async (filters) => {
  const response = await fetch('/api/search', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      ...filters,
      include_filters: true
    })
  });

  const { data } = await response.json();

  return {
    posts: data.results,
    breadcrumbs: data.breadcrumbs,
    availableFilters: data.filters,
    pagination: data.meta
  };
};
```

### Ricerca con Faceted Navigation
```javascript
// Carica filtri disponibili insieme ai risultati
const { data } = await searchPosts({
  collection: 'blog',
  include_filters: true
});

// Mostra filtri con contatori
data.filters.tags.forEach(tag => {
  console.log(`${tag.label} (${tag.count})`);
});
```

---

## Notes

1. **Locale di default**: Se non specificato, usa `it`
2. **Status di default**: Se non specificato, mostra solo `published`
3. **Ordinamento default**: `published_at DESC`
4. **Paginazione default**: 15 risultati per pagina
5. **Ricerca full-text**: Cerca in `title` e in tutti i campi del JSON `data`
6. **Filtri multipli**: Tutti i filtri sono combinati con AND logic
7. **Tags multipli**: Quando specifichi più tags, trova entries che hanno TUTTI i tags
8. **Performance**: Zero query N+1 grazie a eager loading completo
