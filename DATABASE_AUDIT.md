# Database Schema Audit — Cartino

> Generato: 2026-03-07
> Stato: **DA REVISIONARE**
> Scope: migrations Laravel in `/database/migrations`

---

## Indice

1. [Problemi Critici (P1)](#1-problemi-critici-p1)
2. [Problemi Moderati (P2)](#2-problemi-moderati-p2)
3. [Problemi Minori (P3)](#3-problemi-minori-p3)
4. [Cosa funziona bene](#4-cosa-funziona-bene)
5. [Refactoring Priority List](#5-refactoring-priority-list)
6. [Migrations consigliate](#6-migrations-consigliate)
7. [Schema JSON API](#7-schema-json-api)
8. [Eloquent Best Practices](#8-eloquent-best-practices)

---

## 1. Problemi Critici (P1)

---

### 1.1 — Quadruplice sistema di pricing: source of truth non definita

**File coinvolti:**
- `2025_01_01_000840_create_product_variants_table.php` — `price`, `compare_at_price`, `cost` flat
- `2025_01_01_000870_create_variant_prices_table.php` — multi-context pricing
- `2025_01_01_001600_create_advanced_pricing_engine_tables.php` — `price_tiers`
- `2025_01_01_000930_create_catalog_product_table.php` — `fixed_price`, `compare_at_price`
- `2025_01_01_000940_create_catalog_product_variant_table.php` — `fixed_price`, `compare_at_price`

**Problema:**
Quattro tabelle diverse possono rispondere alla domanda "quanto costa questo prodotto?". La gerarchia di risoluzione esiste solo nel codice PHP applicativo, non è formalmente definita nello schema.

| Tabella | Scope | Tier |
|---|---|---|
| `product_variants.price` | flat, nessun contesto | fallback |
| `variant_prices` | site + channel + customer_group + catalog + currency + quantity | principale |
| `price_tiers` | product + variant + customer_group + currency + quantity | duplicato di variant_prices |
| `catalog_product.fixed_price` | override per catalog a livello prodotto | duplicato parziale |
| `catalog_product_variant.fixed_price` | override per catalog a livello variante | duplicato parziale |

**`price_tiers` duplica esattamente `variant_prices`** — stessi campi (`min_quantity`, `max_quantity`, `currency_code`, `customer_group_id`, `valid_from`, `valid_until`). Uno dei due va eliminato.

**Fix consigliato:**
- Elimina `price_tiers` completamente.
- Rimuovi `fixed_price` e `compare_at_price` da `catalog_product` e `catalog_product_variant` — sostituisci con una row in `variant_prices` dove `catalog_id` è valorizzato.
- Mantieni `product_variants.price` come valore di fallback/default denormalizzato con un commento esplicito nello schema.
- Documenta la priorità di risoluzione come commento nella migration `variant_prices`:

```
Priorità risoluzione (più alto = vince):
1. variant_prices con catalog_id + customer_group_id + channel_id (priority 100+)
2. variant_prices con customer_group_id + channel_id (priority 50+)
3. variant_prices con solo channel_id o solo customer_group_id (priority 10+)
4. product_variants.price (fallback, priority 0)
```

---

### 1.2 — Doppio sistema CMS: `entries` vs `collection_entries`

**File coinvolti:**
- `2024_12_12_000002_create_entries_table.php`
- `2025_01_01_002140_create_collection_entries_table.php`

**Problema:**

`entries` (dicembre 2024):
- `collection` e' una **stringa** non una FK su `collections` — nessuna integrita' referenziale
- `locale` e' un campo diretto sulla riga — ogni traduzione crea un record separato con slug/collection duplicati
- Nessun link formale alla tabella `collections`

`collection_entries` (gennaio 2025):
- `collection_id` e' FK su `collections` — corretto
- Nessuna gestione locale/traduzione
- `data` JSON generico senza schema

Sono due sistemi CMS paralleli e incompatibili. Non e' chiaro quale dei due debba essere usato per un blog multilingua, ad esempio.

**Fix consigliato:**
Unificare in un unico sistema: `entries` con FK + `entry_translations` per le localizzazioni.
Vedi migration completa in [sezione 6.1](#61--entries--entry_translations-sostituzione).

---

### 1.3 — Inventory quantity duplicata: source of truth ambigua

**File coinvolti:**
- `2025_01_01_000840_create_product_variants_table.php` — `inventory_quantity` flat
- `2025_01_01_000980_create_inventory_locations_tables.php` — `location_inventories.quantity`

**Problema:**
`product_variants.inventory_quantity` coesiste con `location_inventories.quantity` per location multipla. In un sistema multi-location, il campo flat diventa stale dopo ogni movimento di inventory. Non e' documentato nello schema quale sia la fonte di verita'.

**Fix consigliato:**
Depreca la scrittura diretta su `product_variants.inventory_quantity` — rendila esplicitamente una cache aggregata:

```php
$table->integer('inventory_quantity')
    ->default(0)
    ->comment('CACHE AGGREGATA: somma di location_inventories.available_quantity. Aggiornata da observer. Non scrivere direttamente.');
```

Aggiungere un `ProductVariantObserver` che ricalcola questo campo dopo ogni cambiamento in `location_inventories`.

---

### 1.4 — Tre sistemi paralleli per le opzioni varianti

**File coinvolti:**
- `2025_01_01_000830_create_products_table.php` — `options JSONB` nella tabella products
- `2025_01_01_001200_create_collections_and_options_tables.php` — tabella `product_options`
- `2025_01_01_000840_create_product_variants_table.php` — `option1`, `option2`, `option3`

**Problema:**
Lo stesso dato (opzioni di prodotto) esiste in tre posti:

| Fonte | Tipo | Limite |
|---|---|---|
| `products.options` | JSONB | illimitato ma non queryabile |
| `product_options` | tabella relazionale | normalizzata ma ridondante |
| `product_variants.option1/2/3` | colonne flat | massimo 3 opzioni hardcoded |

Il commento nella migration stessa ammette il problema:
`"Note: New system uses product_variant_option_value table instead"` — ma quella tabella non esiste.

**Fix consigliato:**
Scegliere uno dei due sistemi e abbandonare gli altri. Opzioni:

- **Sistema A (semplice):** mantieni solo `product_options` + `option1/option2/option3` sulle varianti. Limite a 3 opzioni ma semplice da gestire.
- **Sistema B (enterprise):** crea `product_option_values` come pivot tra varianti e valori di opzione — rimuovi `option1/2/3` e il JSONB `options`. Supporta N opzioni.

Se nel futuro si prevede di superare le 3 opzioni per variante, implementare Sistema B adesso.

---

### 1.5 — `collections.channel_id` senza FK constraint

**File coinvolto:** `2025_01_01_001200_create_collections_and_options_tables.php`

**Problema:**
```php
$table->unsignedBigInteger('channel_id'); // riga 15 — nessun constrained()
```

Nessuna integrita' referenziale su `channel_id`. Un channel eliminato lascia collections orfane silenziosamente.

**Fix:**
```php
$table->foreignId('channel_id')->constrained('channels')->cascadeOnDelete();
// oppure nullOnDelete() se una collection puo' esistere senza channel
```

---

### 1.6 — Pivot table `category_products` con nome sbagliato

**File coinvolto:** `2025_01_01_001200_create_collections_and_options_tables.php`

**Problema:**
La pivot tra `collections` e `products` si chiama `category_products` — ma la pivot tra `categories` e `products` si chiama `category_product`. Nomi quasi identici, concetti diversi, confusione garantita.

```
category_products  → pivot collections ↔ products  (nome SBAGLIATO)
category_product   → pivot categories ↔ products   (nome corretto)
```

**Fix:**
Rinominare `category_products` in `collection_products` con una migration dedicata.

---

## 2. Problemi Moderati (P2)

---

### 2.1 — Mancanza di translations per `products`, `collections`, `product_types`

**Problema:**
Le tabelle `products`, `collections` e `product_types` hanno campi di testo localizzabili (`title`, `description`, `excerpt`, `meta_title`, `meta_description`) direttamente sulla riga senza una translations table. Non e' possibile gestire multi-locale senza duplicare i record o usare JSONB per i testi (che compromette il full-text search).

Le migrations menzionate negli obiettivi (`product_translations`, `product_type_translations`) non risultano presenti.

**Fix:**
Creare tabelle translation. Vedi migrations in [sezione 6.2](#62--product_translations) e [sezione 6.3](#63--collection_translations).

---

### 2.2 — `orders.currency_id` (FK) vs `variant_prices.currency` (char 3)

**Problema:**
Gli ordini referenziano la tabella `currencies` tramite FK (`currency_id`). Le tabelle di pricing usano il codice ISO direttamente come `char(3)`. Incoerenza che rende JOIN tra ordini e pricing piu' complessi del necessario.

**Fix:**
Standardizzare su `char(3)` per tutti i riferimenti a valute nelle tabelle di pricing (il codice ISO 4217 e' stabile per design). Eventualmente aggiungere un check constraint:

```php
$table->char('currency', 3);
// CHECK: currency IN (SELECT code FROM currencies)  -- solo se DB lo supporta
```

---

### 2.3 — `collection_entries.slug` nullable senza unique constraint

**File coinvolto:** `2025_01_01_002140_create_collection_entries_table.php`

**Problema:**
Il campo `slug` e' nullable e non ha un unique constraint con `collection_id`. Due entries nella stessa collection possono avere lo stesso slug, rompendo il routing URL.

**Fix:**
```php
$table->string('slug')->nullable();
$table->unique(['collection_id', 'slug']); // aggiungere questo
```

---

### 2.4 — `product_types` senza multi-locale

**Problema:**
`product_types` ha `name` e `description` come campi diretti senza translations. Se i tipi prodotto sono visibili nel frontend (es. filtri, breadcrumb), devono essere localizzabili.

**Fix:**
Creare `product_type_translations`. Vedi [sezione 6.4](#64--product_type_translations).

---

## 3. Problemi Minori (P3)

---

### 3.1 — Overindexing su `orders` e `location_inventories`

**File coinvolto:** `2025_01_01_001300_create_orders_table.php`

Indici inutili o raramente utili:
```php
$table->index('subtotal');         // quasi mai usato da solo
$table->index('tax_total');        // idem
$table->index('shipping_total');   // idem
$table->index('discount_total');   // idem
$table->index('updated_at');       // utile solo se si ordina per updated_at frequentemente
```

Un indice su `subtotal` singolo non viene usato dal query planner se la query filtra prima per `status` o `customer_id`. Ogni indice non usato ha un costo su ogni INSERT/UPDATE.

**Fix:**
Rimuovere gli indici singoli sui campi decimali aggregati. Tenerli solo se ci sono query di reporting specifiche che li usano.

---

### 3.2 — `categories.products_count` — counter cache non sicura

**File coinvolto:** `2025_01_01_000880_create_categories_table.php`

**Problema:**
Campo `products_count` direttamente sulla categoria. Puo' andare out-of-sync con la realta' se non gestita tramite observer o trigger.

**Fix:**
Eliminare il campo e calcolare sempre da query con `withCount('products')`, oppure gestirlo esplicitamente con un `CategoryObserver` che aggiorna il contatore.

---

### 3.3 — `product_variants.inventory_management` default 'shopify'

**File coinvolto:** `2025_01_01_000840_create_product_variants_table.php`

```php
$table->string('inventory_management')->default('shopify');
```

Vendor lock-in nel default di un sistema proprietario. Cambiare in `'managed'` o `'internal'`.

---

### 3.4 — `collections` ha `description` e `body_html` — ridondanza semantica

**File coinvolto:** `2025_01_01_001200_create_collections_and_options_tables.php`

```php
$table->text('description')->nullable();
$table->text('body_html')->nullable();
```

Semanticamente sovrapposti. `body_html` e' probabilmente una vestigia dello stile Shopify. Valutare se tenere entrambi o consolidare in `description` con un tipo WYSIWYG gestito a livello applicativo.

---

### 3.5 — `entries` usa `parent_id` con `cascadeOnDelete`

**File coinvolto:** `2024_12_12_000002_create_entries_table.php`

```php
$table->foreignId('parent_id')->nullable()->constrained('entries')->onDelete('cascade');
```

Se si elimina un'entry parent, tutte le children vengono eliminate a cascata. Questo comportamento e' spesso indesiderato per alberature CMS — si potrebbe preferire `nullOnDelete()` per lasciare le children orfane invece di eliminarle.

---

### 3.6 — Indice `$table->index('requires_selling_plan')` su boolean

**File coinvolto:** `2025_01_01_000830_create_products_table.php`

Un indice su un campo booleano con bassa cardinalita' e' raramente utile — il query planner preferira' uno scan completo se la percentuale di true/false e' distribuita.

---

## 4. Cosa funziona bene

- **`variant_prices`** — struttura di contesto eccellente (site + channel + customer_group + catalog + currency + priority + scheduling). E' il pattern giusto per pricing enterprise multi-market.
- **`location_inventories.available_quantity`** — colonna stored/computed (`quantity - reserved_quantity`) e' la scelta corretta.
- **`inventory_movements`** — morph reference per il tracciamento e' enterprise-grade e permette di collegare movimenti a qualsiasi entita'.
- **Pattern `data JSONB`** su ogni tabella per custom fields — evita le EAV tables (entity-attribute-value) e mantiene la flessibilita'.
- **`category_breadcrumbs`** — closure table per navigazione gerarchica rapida senza ricorsione.
- **`pricing_rules` + `pricing_rule_applications`** — motore di regole ben strutturato con audit trail.
- **`HasJsonFields`, `HasPublishing`, `HasSiteScope`** — i concern riutilizzabili nelle migrations sono un ottimo pattern per la consistenza.
- Indici compositi chiave (`[site_id, status]`, `[product_id, position]`, ecc.) sono tutti presenti nelle tabelle core.
- `softDeletes()` consistente su tutte le tabelle principali.

---

## 5. Refactoring Priority List

| # | Priorita' | Azione | File da modificare |
|---|---|---|---|
| 1 | P1 CRITICO | Unifica `entries` + `collection_entries` → entries con FK + `entry_translations` | `2024_12_12_000002`, `2025_01_01_002140` |
| 2 | P1 CRITICO | Elimina `price_tiers` (duplica `variant_prices`) | `2025_01_01_001600` |
| 3 | P1 CRITICO | Aggiungi FK constraint su `collections.channel_id` | `2025_01_01_001200` |
| 4 | P1 CRITICO | Rinomina `category_products` → `collection_products` | `2025_01_01_001200` |
| 5 | P1 CRITICO | Rimuovi `fixed_price` da `catalog_product` e `catalog_product_variant` | `2025_01_01_000930`, `2025_01_01_000940` |
| 6 | P2 ALTO | Crea `product_translations` | nuova migration |
| 7 | P2 ALTO | Crea `collection_translations` | nuova migration |
| 8 | P2 ALTO | Crea `product_type_translations` | nuova migration |
| 9 | P2 ALTO | Scegli un sistema opzioni varianti e rimuovi gli altri due | `000830`, `001200`, `000840` |
| 10 | P2 ALTO | Aggiungi unique(`collection_id`, `slug`) su `collection_entries` | `2025_01_01_002140` |
| 11 | P3 BASSO | Documenta `inventory_quantity` come cache aggregata | `000840` |
| 12 | P3 BASSO | Rimuovi indici singoli inutili su `orders` | `2025_01_01_001300` |
| 13 | P3 BASSO | Cambia default `inventory_management` da 'shopify' a 'managed' | `000840` |
| 14 | P3 BASSO | Valuta rimozione `categories.products_count` | `000880` |

---

## 6. Migrations consigliate

---

### 6.1 — entries + entry_translations (sostituzione)

Sostituisce sia `entries` che `collection_entries` con un sistema unificato.

```php
<?php
// database/migrations/XXXX_create_entries_unified_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entries', function (Blueprint $table) {
            $table->id();

            // Relazione con la collection (FK, non stringa)
            $table->foreignId('collection_id')->constrained('collections')->cascadeOnDelete();

            // Slug univoco per collection (locale-agnostic — il titolo localizzato sta in translations)
            $table->string('slug');

            // Gerarchia
            $table->foreignId('parent_id')->nullable()->constrained('entries')->nullOnDelete();
            $table->integer('order')->default(0);

            // Stato e pubblicazione
            $table->string('status')->default('draft'); // draft, published, scheduled, archived
            $table->timestamp('published_at')->nullable();

            // Autore
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['collection_id', 'slug']);
            $table->index(['collection_id', 'status']);
            $table->index(['collection_id', 'published_at']);
            $table->index(['status', 'published_at']);
            $table->index('parent_id');
            $table->index('order');
        });

        Schema::create('entry_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entry_id')->constrained('entries')->cascadeOnDelete();
            $table->string('locale', 10); // it, en, fr, de

            // Contenuto localizzato
            $table->string('title');
            $table->json('data')->nullable(); // custom fields localizzati

            // SEO localizzato
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();

            $table->timestamps();

            $table->unique(['entry_id', 'locale']);
            $table->index(['locale', 'entry_id']);
            $table->index('locale');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entry_translations');
        Schema::dropIfExists('entries');
    }
};
```

---

### 6.2 — product_translations

```php
<?php
// database/migrations/XXXX_create_product_translations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('locale', 10);

            // Contenuto localizzato
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->text('description')->nullable();

            // SEO localizzato
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();

            $table->timestamps();

            $table->unique(['product_id', 'locale']);
            $table->index(['locale', 'product_id']);
        });

        // Translations anche per le varianti (title della variante e' localizzabile)
        Schema::create('product_variant_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->string('locale', 10);

            $table->string('title'); // es. "Rosso / L" in italiano, "Red / L" in inglese
            $table->timestamps();

            $table->unique(['product_variant_id', 'locale']);
            $table->index('locale');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_translations');
        Schema::dropIfExists('product_translations');
    }
};
```

---

### 6.3 — collection_translations

```php
<?php
// database/migrations/XXXX_create_collection_translations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('collections')->cascadeOnDelete();
            $table->string('locale', 10);

            $table->string('title');
            $table->text('description')->nullable();

            // SEO localizzato
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();

            $table->unique(['collection_id', 'locale']);
            $table->index(['locale', 'collection_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_translations');
    }
};
```

---

### 6.4 — product_type_translations

```php
<?php
// database/migrations/XXXX_create_product_type_translations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_type_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_type_id')->constrained('product_types')->cascadeOnDelete();
            $table->string('locale', 10);

            $table->string('name');
            $table->text('description')->nullable();

            $table->timestamps();

            $table->unique(['product_type_id', 'locale']);
            $table->index('locale');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_type_translations');
    }
};
```

---

### 6.5 — collection_products (rinomina da category_products)

```php
<?php
// database/migrations/XXXX_rename_category_products_to_collection_products.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('category_products', 'collection_products');
    }

    public function down(): void
    {
        Schema::rename('collection_products', 'category_products');
    }
};
```

---

### 6.6 — Rimozione price_tiers (duplicato di variant_prices)

```php
<?php
// database/migrations/XXXX_drop_price_tiers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // price_tiers e' un duplicato di variant_prices.
        // Migrare i dati in variant_prices prima di eseguire questa migration.
        // Script di migrazione dati: vedere docs/migrations/price-tiers-to-variant-prices.md
        Schema::dropIfExists('price_tiers');
    }

    public function down(): void
    {
        // Non e' possibile ripristinare i dati eliminati senza backup.
        // Tenere backup prima di eseguire questa migration in produzione.
    }
};
```

---

### 6.7 — Fix FK su collections.channel_id e unique su collection_entries.slug

```php
<?php
// database/migrations/XXXX_fix_collections_integrity.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix 1: FK su collections.channel_id
        Schema::table('collections', function (Blueprint $table) {
            $table->foreign('channel_id')
                ->references('id')
                ->on('channels')
                ->cascadeOnDelete();
        });

        // Fix 2: unique constraint su collection_entries(collection_id, slug)
        Schema::table('collection_entries', function (Blueprint $table) {
            // Prima rimuovere i duplicati se esistono in produzione
            $table->unique(['collection_id', 'slug'], 'collection_entries_collection_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('collection_entries', function (Blueprint $table) {
            $table->dropUnique('collection_entries_collection_slug_unique');
        });

        Schema::table('collections', function (Blueprint $table) {
            $table->dropForeign(['channel_id']);
        });
    }
};
```

---

## 7. Schema JSON API

Schema consigliato per API headless. Il pricing e l'inventory sono sempre risolti lato server prima della risposta.

```json
{
  "product": {
    "id": 1,
    "handle": "t-shirt-blue",
    "slug": "t-shirt-blu",
    "locale": "it",
    "site_id": 1,
    "channel_id": 1,
    "status": "published",
    "visibility": "everywhere",
    "condition": "new",
    "brand": {
      "id": 2,
      "name": "Acme",
      "slug": "acme"
    },
    "product_type": {
      "id": 1,
      "slug": "abbigliamento",
      "name": "Abbigliamento"
    },
    "title": "T-Shirt Blu",
    "excerpt": "Breve descrizione.",
    "description": "<p>Descrizione completa in HTML.</p>",
    "tags": ["estate", "cotone", "uomo"],
    "options": [
      { "id": 1, "name": "Colore", "position": 1, "values": ["Rosso", "Blu", "Verde"] },
      { "id": 2, "name": "Taglia", "position": 2, "values": ["S", "M", "L", "XL"] }
    ],
    "seo": {
      "title": "T-Shirt Blu | Shop",
      "description": "Acquista la T-Shirt Blu.",
      "canonical": "/prodotti/t-shirt-blu"
    },
    "variants": [
      {
        "id": 10,
        "sku": "TSH-BLU-S",
        "barcode": "1234567890123",
        "title": "Blu / S",
        "options": {
          "Colore": "Blu",
          "Taglia": "S"
        },
        "pricing": {
          "currency": "EUR",
          "price": "29.99",
          "compare_at_price": "39.99",
          "cost": null,
          "tax_included": false,
          "taxable": true
        },
        "inventory": {
          "total_available": 42,
          "track_quantity": true,
          "policy": "deny",
          "allow_out_of_stock_purchases": false,
          "by_location": [
            {
              "location_id": 1,
              "location_name": "Magazzino Milano",
              "available": 30,
              "incoming": 0
            },
            {
              "location_id": 2,
              "location_name": "Magazzino Roma",
              "available": 12,
              "incoming": 10
            }
          ]
        },
        "shipping": {
          "requires_shipping": true,
          "weight": 0.3,
          "weight_unit": "kg",
          "dimensions": {
            "length": 30,
            "width": 20,
            "height": 2,
            "unit": "cm"
          }
        },
        "status": "active",
        "position": 1
      }
    ],
    "assets": [
      {
        "id": 1,
        "url": "/storage/assets/tshirt-blu.jpg",
        "type": "image",
        "alt": "T-Shirt Blu - vista frontale",
        "position": 1,
        "width": 1200,
        "height": 1200
      }
    ],
    "categories": [
      { "id": 5, "name": "Magliette", "slug": "magliette", "path": "abbigliamento/magliette" }
    ],
    "collections": [
      { "id": 3, "title": "Nuovi Arrivi", "slug": "nuovi-arrivi" }
    ],
    "meta": {
      "min_order_quantity": 1,
      "order_increment": 1,
      "is_closeout": false,
      "hs_code": "6109100010",
      "country_of_origin": "IT"
    },
    "published_at": "2025-01-15T09:00:00Z",
    "created_at": "2025-01-01T00:00:00Z",
    "updated_at": "2026-03-07T10:00:00Z"
  }
}
```

**Risposta lista prodotti (paginata):**
```json
{
  "data": [ /* array di product objects senza variants espansi */ ],
  "meta": {
    "current_page": 1,
    "per_page": 24,
    "total": 847,
    "last_page": 36
  },
  "links": {
    "first": "/api/products?page=1",
    "last": "/api/products?page=36",
    "prev": null,
    "next": "/api/products?page=2"
  }
}
```

---

## 8. Eloquent Best Practices

---

### 8.1 — Risoluzione del prezzo con scope

```php
// app/Models/ProductVariant.php

public function scopeWithResolvedPrice(
    Builder $query,
    string $currency,
    ?int $siteId = null,
    ?int $channelId = null,
    ?int $customerGroupId = null,
    ?int $catalogId = null,
): Builder {
    return $query->with(['prices' => function ($q) use ($currency, $siteId, $channelId, $customerGroupId, $catalogId) {
        $q->where('currency', $currency)
          ->where(fn($q) => $q->whereNull('site_id')->orWhere('site_id', $siteId))
          ->where(fn($q) => $q->whereNull('channel_id')->orWhere('channel_id', $channelId))
          ->where(fn($q) => $q->whereNull('customer_group_id')->orWhere('customer_group_id', $customerGroupId))
          ->where(fn($q) => $q->whereNull('catalog_id')->orWhere('catalog_id', $catalogId))
          ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
          ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
          ->orderByDesc('priority')
          ->limit(1);
    }]);
}

public function getResolvedPriceAttribute(): ?VariantPrice
{
    return $this->prices->first();
}

public function getEffectivePriceAttribute(): string
{
    return $this->resolvedPrice?->price ?? $this->price;
}
```

---

### 8.2 — Translation accessor con fallback

```php
// app/Models/Product.php

public function translations(): HasMany
{
    return $this->hasMany(ProductTranslation::class);
}

public function translation(string $locale = null): ?ProductTranslation
{
    $locale ??= app()->getLocale();

    return $this->translations->firstWhere('locale', $locale)
        ?? $this->translations->firstWhere('locale', config('app.fallback_locale'));
}

// Accessor comodi
public function getTitleAttribute(): string
{
    return $this->translation()?->title ?? $this->attributes['title'] ?? '';
}

// Alternativa: usare spatie/laravel-translatable che gestisce tutto questo automaticamente
```

---

### 8.3 — Inventory aggregata

```php
// app/Models/ProductVariant.php

public function locationInventories(): HasMany
{
    return $this->hasMany(LocationInventory::class);
}

public function getTotalAvailableQuantityAttribute(): int
{
    // Usa il campo cached se disponibile e aggiornato
    // altrimenti calcola dalla relazione
    return $this->locationInventories->sum('available_quantity');
}

public function getInventoryByLocationAttribute(): Collection
{
    return $this->locationInventories->map(fn($inv) => [
        'location_id'   => $inv->location_id,
        'location_name' => $inv->location->name,
        'available'     => $inv->available_quantity,
        'incoming'      => $inv->incoming_quantity,
    ]);
}
```

---

### 8.4 — Observer per sincronizzazione inventory_quantity

```php
// app/Observers/LocationInventoryObserver.php

class LocationInventoryObserver
{
    public function saved(LocationInventory $inventory): void
    {
        $this->syncVariantQuantity($inventory->product_variant_id);
    }

    public function deleted(LocationInventory $inventory): void
    {
        $this->syncVariantQuantity($inventory->product_variant_id);
    }

    private function syncVariantQuantity(int $variantId): void
    {
        $total = LocationInventory::where('product_variant_id', $variantId)
            ->sum('available_quantity');

        ProductVariant::where('id', $variantId)
            ->update(['inventory_quantity' => $total]);
    }
}
```

---

### 8.5 — Query ottimizzata per listing prodotti

```php
// Carica tutto in un'unica query composta senza N+1

Product::query()
    ->with([
        'brand:id,name,slug',
        'productType:id,slug',
        'productType.translations' => fn($q) => $q->where('locale', app()->getLocale()),
        'translations' => fn($q) => $q->where('locale', app()->getLocale()),
        'assets' => fn($q) => $q->where('type', 'image')->orderBy('position')->limit(1),
        'variants' => fn($q) => $q->where('status', 'active')->orderBy('position'),
        'variants.prices' => fn($q) => $q
            ->where('currency', session('currency', 'EUR'))
            ->orderByDesc('priority')
            ->limit(1),
    ])
    ->where('site_id', currentSiteId())
    ->where('status', 'published')
    ->where('visibility', '!=', 'none')
    ->orderByDesc('created_at')
    ->paginate(24);
```

---

### 8.6 — Pacchetti consigliati

| Pacchetto | Uso |
|---|---|
| `spatie/laravel-translatable` | Gestione translations con accessor automatici, fallback locale, scoping |
| `spatie/laravel-medialibrary` | Gestione assets/media con conversioni automatiche |
| `spatie/laravel-sluggable` | Slug auto-generati con unique handling |
| `owen-it/laravel-auditing` | Audit log automatico su tutti i modelli |

---

## Note finali

- Tutte le modifiche P1 richiedono migration incrementali (non modificare le esistenti in produzione).
- Prima di eseguire `DROP TABLE price_tiers`, verificare che nessun codice PHP la referenzi.
- Prima di aggiungere il unique constraint su `collection_entries(collection_id, slug)`, verificare e pulire eventuali duplicati esistenti.
- Il sistema di translations e' indipendente da `spatie/laravel-translatable` — si puo' implementare manualmente o usare il pacchetto.

---

*Audit generato da revisione automatica delle migrations. Richiede validazione manuale da parte del team.*
