# Vocabularies System

## 📋 Indice

1. [Introduzione](#introduzione)
2. [Architettura](#architettura)
3. [Setup Iniziale](#setup-iniziale)
4. [Uso Base](#uso-base)
5. [Integrazioni](#integrazioni)
6. [Estendibilità](#estendibilit%C3%A0)
7. [Best Practices](#best-practices)

---

## Introduzione

Il sistema **Vocabularies** di Cartino fornisce un approccio **database-based** per gestire:

✅ Stati ordine customizzabili
✅ Traduzioni multi-lingua integrate
✅ Select riutilizzabili in Inertia/Vue
✅ Estensibilità senza modifiche al codice
✅ Metadata flessibile (colori, workflow, flags)

### Perché DB invece di Enum PHP?

| Aspetto | Enum PHP | Vocabularies DB |
|---------|----------|-----------------|
| Modifiche | Richiede deploy | Immediato (admin panel) |
| Traduzioni | File separati | Integrate nella tabella |
| Estensibilità | Service provider complessi | INSERT nella tabella |
| Multi-tenant | Difficile | Nativo |
| Frontend | Serve mapping manuale | Automatico via Inertia |

---

## Architettura

### Struttura Tabella

```sql
vocabularies
------------
id               BIGINT UNSIGNED
group            VARCHAR(50)      -- order_status, payment_status, ecc.
code             VARCHAR(50)      -- pending, paid, shipped, ecc.
labels           JSON             -- {"it":"In attesa","en":"Pending"}
sort_order       INT              -- Ordine di visualizzazione
meta             JSON NULL        -- {"color":"orange","is_final":true}
is_system        BOOLEAN          -- Se true, non eliminabile
is_active        BOOLEAN          -- Attivo/disattivo
created_at       TIMESTAMP
updated_at       TIMESTAMP

UNIQUE (group, code)
```

### Gruppi Predefiniti

- `order_status` - Stati ordine
- `payment_status` - Stati pagamento
- `fulfillment_status` - Stati evasione
- `shipping_status` - Stati spedizione
- `return_status` - Stati reso
- `product_type` - Tipologie prodotto
- `stock_status` - Disponibilità scorte

---

## Setup Iniziale

### 1. Esegui Migration

```bash
php artisan migrate
```

### 2. Esegui Seeder

```bash
php artisan db:seed --class=VocabularySeeder
```

Questo popola la tabella con i valori predefiniti per tutti i gruppi.

### 3. Registra Middleware Inertia

In [`src/Http/Kernel.php`](../src/Http/Kernel.php) o nel tuo service provider:

```php
use Cartino\Http\Middleware\ShareVocabularies;

// Aggiungi al gruppo 'web'
protected $middlewareGroups = [
    'web' => [
        // ...
        \Cartino\Http\Middleware\ShareVocabularies::class,
    ],
];
```

### 4. Registra Service

In [`src/CartinoServiceProvider.php`](../src/CartinoServiceProvider.php):

```php
use Cartino\Services\VocabularyService;

public function register(): void
{
    $this->app->singleton(VocabularyService::class);
}
```

---

## Uso Base

### Model: Relazioni con Vocabulary

```php
use Cartino\Models\Vocabulary;

class Order extends Model
{
    public function statusVocabulary(): BelongsTo
    {
        return $this->belongsTo(Vocabulary::class, 'status', 'code')
            ->where('group', 'order_status');
    }
}

// Uso
$order->statusVocabulary->getLabel(); // "In attesa"
$order->statusVocabulary->getColor(); // "orange"
```

### Controller: Fornire Dati per Select

```php
use Cartino\Models\Vocabulary;

// Opzione 1: Direttamente dal model
$statuses = Vocabulary::getSelectOptions('order_status');

// Opzione 2: Tramite service
$statuses = app(VocabularyService::class)->getGroup('order_status');

// Risultato:
[
    ['value' => 'pending', 'label' => 'In attesa', 'color' => 'orange', 'meta' => [...]],
    ['value' => 'confirmed', 'label' => 'Confermato', 'color' => 'blue', 'meta' => [...]],
    // ...
]
```

### Frontend: Vue/Inertia

I vocabularies sono **automaticamente condivisi** tramite middleware Inertia.

#### Componente Select

```vue
<script setup>
import { usePage } from '@inertiajs/vue3'

const { vocabularies } = usePage().props

// Accesso ai vocabolari
const orderStatuses = vocabularies.order_status
const paymentStatuses = vocabularies.payment_status
</script>

<template>
  <select v-model="form.status">
    <option
      v-for="status in orderStatuses"
      :key="status.value"
      :value="status.value"
    >
      {{ status.label }}
    </option>
  </select>
</template>
```

#### Con Badge Colorato

```vue
<template>
  <span
    :class="`badge badge-${status.color}`"
  >
    {{ status.label }}
  </span>
</template>

<script setup>
const props = defineProps({
  status: Object // { value: 'pending', label: 'In attesa', color: 'orange' }
})
</script>
```

---

## Integrazioni

### Validazione Form Request

```php
use Cartino\Models\Vocabulary;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    public function rules(): array
    {
        $validStatuses = Vocabulary::query()
            ->group('order_status')
            ->active()
            ->pluck('code')
            ->toArray();

        return [
            'status' => ['required', Rule::in($validStatuses)],
        ];
    }
}
```

### Workflow di Transizione

```php
// Nel model Order
public function canTransitionTo(string $newStatus): bool
{
    return $this->statusVocabulary?->canTransitionTo($newStatus) ?? true;
}

// Uso
if ($order->canTransitionTo('shipped')) {
    $order->update(['status' => 'shipped']);
}
```

Configurazione in seeder:

```php
'meta' => [
    'allowed_transitions' => ['confirmed', 'cancelled'],
]
```

### Cache

Il model `Vocabulary` ha cache automatica con tag Laravel:

```php
// Pulire cache dopo modifiche
Cache::tags(['vocabularies'])->flush();
```

---

## Estendibilità

### Aggiungere Nuovi Vocabolari via Database

#### Opzione 1: Tramite Seeder Personalizzato

```php
namespace App\Database\Seeders;

use Cartino\Models\Vocabulary;
use Illuminate\Database\Seeder;

class CustomVocabularySeeder extends Seeder
{
    public function run(): void
    {
        Vocabulary::createOrUpdate('order_status', 'on_hold', [
            'labels' => [
                'en' => 'On Hold',
                'it' => 'In sospeso',
            ],
            'sort_order' => 25,
            'meta' => [
                'color' => 'yellow',
                'is_final' => false,
                'allowed_transitions' => ['processing', 'cancelled'],
            ],
            'is_system' => false,
            'is_active' => true,
        ]);
    }
}
```

#### Opzione 2: Tramite Admin Panel (da implementare)

```php
// Controller esempio
public function store(Request $request)
{
    $validated = $request->validate([
        'group' => 'required|string|max:50',
        'code' => 'required|string|max:50',
        'labels' => 'required|array',
        'labels.it' => 'required|string',
        'labels.en' => 'required|string',
        'sort_order' => 'nullable|integer',
        'meta' => 'nullable|array',
    ]);

    Vocabulary::createOrUpdate(
        $validated['group'],
        $validated['code'],
        $validated
    );

    return back()->with('success', 'Vocabulary created successfully');
}
```

#### Opzione 3: Via Service

```php
use Cartino\Services\VocabularyService;

app(VocabularyService::class)->createOrUpdate('order_status', 'custom_status', [
    'labels' => ['en' => 'Custom Status', 'it' => 'Stato personalizzato'],
    'sort_order' => 100,
    'meta' => ['color' => 'pink'],
]);
```

### Aggiungere Nuovi Gruppi

```php
// 1. Aggiungi al VocabularyService
protected array $commonGroups = [
    'order_status',
    'payment_status',
    // ... esistenti
    'custom_group', // <-- nuovo gruppo
];

// 2. Crea seeder
protected function seedCustomGroup(): void
{
    $items = [
        [
            'code' => 'value1',
            'labels' => ['en' => 'Value 1', 'it' => 'Valore 1'],
            'sort_order' => 10,
            'is_system' => true,
        ],
    ];

    foreach ($items as $item) {
        Vocabulary::createOrUpdate('custom_group', $item['code'], $item);
    }
}

// 3. Usa nel model
public function customVocabulary(): BelongsTo
{
    return $this->belongsTo(Vocabulary::class, 'custom_field', 'code')
        ->where('group', 'custom_group');
}
```

### Multi-Tenant / Multi-Site

Per supportare vocabolari diversi per sito:

```php
// Migration: aggiungi colonna site_id
Schema::table('vocabularies', function (Blueprint $table) {
    $table->foreignId('site_id')->nullable()->constrained();
});

// Model: scope per sito
public function scopeForSite(Builder $query, ?int $siteId = null): Builder
{
    $siteId = $siteId ?? auth()->user()->site_id;

    return $query->where(function ($q) use ($siteId) {
        $q->where('site_id', $siteId)
          ->orWhereNull('site_id'); // Vocabolari globali
    });
}

// Uso
Vocabulary::forSite()->group('order_status')->get();
```

---

## Best Practices

### ✅ DO

1. **Usa `is_system = true`** per vocabolari core che non devono essere eliminati
2. **Specifica `sort_order`** per controllare l'ordine nelle select
3. **Usa metadata** per informazioni aggiuntive (colori, icone, flags)
4. **Cache**: affidati alla cache integrata del model
5. **Traduzioni**: aggiungi sempre almeno `en` e `it` in `labels`
6. **Workflow**: usa `allowed_transitions` per validare cambi di stato

### ❌ DON'T

1. **Non hardcodare** i valori nei controller - usa sempre il DB
2. **Non eliminare** vocabolari con `is_system = true`
3. **Non duplicare** enum PHP per gli stessi concetti
4. **Non bypassare** il VocabularyService per operazioni comuni
5. **Non dimenticare** di aggiornare i seeder quando aggiungi vocabolari system

### Checklist per Nuovi Vocabolari

- [ ] Aggiungi al seeder appropriato
- [ ] Definisci `labels` per tutte le lingue supportate
- [ ] Imposta `sort_order` logico
- [ ] Aggiungi `meta` se necessario (colore, workflow)
- [ ] Marca `is_system = true` se è un vocabolario core
- [ ] Aggiorna `VocabularyService` se è un gruppo comune
- [ ] Aggiungi al middleware se deve essere globale in Inertia
- [ ] Scrivi test per validazione e workflow

---

## Testing

### Test di Base

```php
use Cartino\Models\Vocabulary;

test('can retrieve vocabulary by group and code', function () {
    $vocab = Vocabulary::findByGroupAndCode('order_status', 'pending');

    expect($vocab)
        ->not->toBeNull()
        ->and($vocab->getLabel('it'))->toBe('In attesa')
        ->and($vocab->getColor())->toBe('orange');
});

test('can transition between allowed statuses', function () {
    $pending = Vocabulary::findByGroupAndCode('order_status', 'pending');

    expect($pending->canTransitionTo('confirmed'))->toBeTrue()
        ->and($pending->canTransitionTo('delivered'))->toBeFalse();
});
```

### Test di Integrazione con Model

```php
test('order has vocabulary relationships', function () {
    $order = Order::factory()->create(['status' => 'pending']);

    expect($order->statusVocabulary)->not->toBeNull()
        ->and($order->status_label)->toBe('In attesa')
        ->and($order->status_color)->toBe('orange');
});
```

---

## Esempi Completi

### Esempio 1: Form di Creazione Ordine

**Controller**:
```php
use Cartino\Services\VocabularyService;
use Inertia\Inertia;

public function create(VocabularyService $vocabularies)
{
    return Inertia::render('Orders/Create', [
        'statuses' => $vocabularies->getGroup('order_status'),
        'paymentStatuses' => $vocabularies->getGroup('payment_status'),
    ]);
}
```

**Vue Component**:
```vue
<script setup>
defineProps({
  statuses: Array,
  paymentStatuses: Array,
})

const form = useForm({
  status: 'pending',
  payment_status: 'pending',
})
</script>

<template>
  <form @submit.prevent="form.post('/orders')">
    <select v-model="form.status">
      <option v-for="s in statuses" :key="s.value" :value="s.value">
        {{ s.label }}
      </option>
    </select>
  </form>
</template>
```

### Esempio 2: Badge di Stato con Colore

**Component Riutilizzabile**:
```vue
<script setup>
const props = defineProps({
  status: String,
  group: {
    type: String,
    default: 'order_status'
  }
})

const { vocabularies } = usePage().props

const statusData = computed(() => {
  const vocab = vocabularies[props.group]
  return vocab?.find(v => v.value === props.status)
})
</script>

<template>
  <span
    v-if="statusData"
    class="px-2 py-1 rounded text-sm"
    :class="`bg-${statusData.color}-100 text-${statusData.color}-800`"
  >
    {{ statusData.label }}
  </span>
  <span v-else>{{ status }}</span>
</template>
```

**Uso**:
```vue
<StatusBadge :status="order.status" group="order_status" />
```

---

## FAQ

### Q: Posso ancora usare enum PHP per altri casi?

**A:** Sì! Usa enum PHP per:
- Ruoli di sistema (`admin`, `user`)
- Feature flags
- Costanti tecniche

Non usarli per concetti di business che cambiano (stati, tipologie, ecc.).

### Q: Come gestisco le migrazioni quando cambio un vocabolario?

**A:** Non serve migrazione! Aggiorna direttamente nel DB o tramite seeder. Per deployment:

```php
// In un seeder versionato
public function run(): void
{
    // Aggiorna label esistente
    Vocabulary::where('group', 'order_status')
        ->where('code', 'pending')
        ->update([
            'labels->it' => 'Nuova traduzione',
        ]);
}
```

### Q: Performance: meglio eager loading?

**A:** Sì, usa eager loading per evitare N+1:

```php
$orders = Order::with(['statusVocabulary', 'paymentStatusVocabulary'])->get();
```

La cache dei vocabolari riduce comunque le query.

---

## Conclusione

Il sistema Vocabularies offre:

✅ **Flessibilità** - Modifiche senza deploy
✅ **Internazionalizzazione** - Traduzioni integrate
✅ **Estendibilità** - Semplice aggiungere vocabolari
✅ **Type-Safety** - Validazione via DB
✅ **UX** - Select automatiche in frontend

È la soluzione ideale per e-commerce dove i requisiti di business evolvono frequentemente.

---

**Documentazione generata per Cartino v1.0**
Ultimo aggiornamento: 2025-12-23
