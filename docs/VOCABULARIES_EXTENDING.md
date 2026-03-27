# 🔌 Extending Vocabularies - Plugin Guide

Guida completa per **estendere i vocabolari** via plugin/addon senza modificare il core di Cartino.

---

## 📦 Scenario: Plugin che Aggiunge Stati Ordine Custom

### Struttura Plugin

```
addons/
  custom-order-statuses/
    plugin.json
    src/
      CustomOrderStatusesPlugin.php
      Seeders/
        CustomVocabularySeeder.php
```

---

## 1️⃣ File: `plugin.json`

```json
{
  "id": "custom-order-statuses",
  "name": "Custom Order Statuses",
  "description": "Adds custom order statuses for warehouse management",
  "version": "1.0.0",
  "author": "Your Company",
  "class": "CustomOrderStatuses\\CustomOrderStatusesPlugin",
  "dependencies": {},
  "autoload": {
    "psr-4": {
      "CustomOrderStatuses\\": "src/"
    }
  }
}
```

---

## 2️⃣ File: `CustomOrderStatusesPlugin.php`

```php
<?php

namespace CustomOrderStatuses;

use Cartino\Models\Vocabulary;
use Illuminate\Support\ServiceProvider;

class CustomOrderStatusesPlugin extends ServiceProvider
{
    /**
     * Bootstrap plugin services.
     */
    public function boot(): void
    {
        // Registra seeder del plugin
        $this->loadSeeders();

        // Aggiungi stati custom al boot
        $this->registerCustomStatuses();
    }

    /**
     * Register plugin services.
     */
    public function register(): void
    {
        // Registra eventuali servizi custom
    }

    /**
     * Carica seeder del plugin.
     */
    protected function loadSeeders(): void
    {
        $this->publishes([
            __DIR__.'/Seeders' => database_path('seeders/Plugins'),
        ], 'custom-vocabularies-seeders');
    }

    /**
     * Registra stati ordine custom.
     */
    protected function registerCustomStatuses(): void
    {
        // Esegui solo in produzione/staging, non in test
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return;
        }

        $this->addWarehouseStatuses();
        $this->addQualityCheckStatuses();
    }

    /**
     * Aggiungi stati per gestione magazzino.
     */
    protected function addWarehouseStatuses(): void
    {
        $statuses = [
            [
                'code' => 'awaiting_stock',
                'labels' => [
                    'en' => 'Awaiting Stock',
                    'it' => 'In attesa di stock',
                ],
                'sort_order' => 25,
                'meta' => [
                    'color' => 'amber',
                    'is_final' => false,
                    'allowed_transitions' => ['processing', 'cancelled'],
                    'notify_warehouse' => true,
                ],
                'is_system' => false,
                'is_active' => true,
            ],
            [
                'code' => 'in_warehouse',
                'labels' => [
                    'en' => 'In Warehouse',
                    'it' => 'In magazzino',
                ],
                'sort_order' => 35,
                'meta' => [
                    'color' => 'cyan',
                    'is_final' => false,
                    'allowed_transitions' => ['shipped', 'quality_check'],
                ],
                'is_system' => false,
                'is_active' => true,
            ],
        ];

        foreach ($statuses as $status) {
            Vocabulary::createOrUpdate('order_status', $status['code'], $status);
        }
    }

    /**
     * Aggiungi stati per controllo qualità.
     */
    protected function addQualityCheckStatuses(): void
    {
        $statuses = [
            [
                'code' => 'quality_check',
                'labels' => [
                    'en' => 'Quality Check',
                    'it' => 'Controllo qualità',
                ],
                'sort_order' => 32,
                'meta' => [
                    'color' => 'purple',
                    'is_final' => false,
                    'allowed_transitions' => ['processing', 'quality_failed'],
                    'requires_approval' => true,
                ],
                'is_system' => false,
                'is_active' => true,
            ],
            [
                'code' => 'quality_failed',
                'labels' => [
                    'en' => 'Quality Check Failed',
                    'it' => 'Controllo qualità fallito',
                ],
                'sort_order' => 33,
                'meta' => [
                    'color' => 'red',
                    'is_final' => true,
                    'allowed_transitions' => [],
                    'notify_admin' => true,
                ],
                'is_system' => false,
                'is_active' => true,
            ],
        ];

        foreach ($statuses as $status) {
            Vocabulary::createOrUpdate('order_status', $status['code'], $status);
        }
    }
}
```

---

## 3️⃣ File: `CustomVocabularySeeder.php` (Opzionale)

Utile per installazione iniziale del plugin:

```php
<?php

namespace CustomOrderStatuses\Seeders;

use Cartino\Models\Vocabulary;
use Illuminate\Database\Seeder;

class CustomVocabularySeeder extends Seeder
{
    public function run(): void
    {
        $this->seedWarehouseStatuses();
        $this->seedQualityStatuses();
    }

    protected function seedWarehouseStatuses(): void
    {
        // Stesso codice di addWarehouseStatuses() nel plugin
    }

    protected function seedQualityStatuses(): void
    {
        // Stesso codice di addQualityCheckStatuses() nel plugin
    }
}
```

---

## 4️⃣ Installazione del Plugin

### Automatica (al boot)

Gli stati vengono registrati automaticamente quando il plugin viene caricato.

### Manuale (via seeder)

```bash
php artisan db:seed --class=CustomOrderStatuses\\Seeders\\CustomVocabularySeeder
```

---

## 🎯 Uso nel Frontend (automatico!)

Una volta aggiunti, i nuovi stati **appaiono automaticamente** nelle select Inertia:

```vue
<script setup>
import { usePage } from '@inertiajs/vue3'

const { vocabularies } = usePage().props

// I nuovi stati sono già inclusi!
const orderStatuses = vocabularies.order_status
// [
//   { value: 'pending', label: 'In attesa' },
//   { value: 'awaiting_stock', label: 'In attesa di stock' }, // ← NUOVO
//   { value: 'quality_check', label: 'Controllo qualità' },    // ← NUOVO
//   ...
// ]
</script>
```

---

## 🔧 Personalizzazioni Avanzate

### Aggiungere Nuovi Gruppi di Vocabolari

```php
// Nel plugin
protected function registerCustomGroups(): void
{
    $priorities = [
        [
            'code' => 'high',
            'labels' => ['en' => 'High Priority', 'it' => 'Priorità alta'],
            'sort_order' => 10,
            'meta' => ['color' => 'red', 'notify_immediately' => true],
        ],
        [
            'code' => 'normal',
            'labels' => ['en' => 'Normal', 'it' => 'Normale'],
            'sort_order' => 20,
            'meta' => ['color' => 'blue'],
        ],
        [
            'code' => 'low',
            'labels' => ['en' => 'Low Priority', 'it' => 'Priorità bassa'],
            'sort_order' => 30,
            'meta' => ['color' => 'gray'],
        ],
    ];

    foreach ($priorities as $priority) {
        Vocabulary::createOrUpdate('order_priority', $priority['code'], $priority);
    }
}
```

### Modificare Vocabolari Esistenti

```php
protected function updateExistingVocabularies(): void
{
    // Aggiungi metadata custom a uno stato esistente
    $pending = Vocabulary::findByGroupAndCode('order_status', 'pending');

    if ($pending) {
        $meta = $pending->meta ?? [];
        $meta['send_reminder_after_hours'] = 24;

        $pending->update(['meta' => $meta]);
    }
}
```

### Condivisione via Inertia di Gruppi Custom

Se vuoi che il nuovo gruppo appaia automaticamente in Inertia, estendi `VocabularyService`:

```php
// Nel plugin boot()
$this->app->extend(VocabularyService::class, function ($service) {
    // Aggiungi gruppo custom ai comuni
    $service->addCommonGroup('order_priority');
    return $service;
});
```

---

## 🧪 Testing del Plugin

```php
<?php

namespace Tests\Feature\Plugins;

use Cartino\Models\Vocabulary;
use Tests\TestCase;

class CustomOrderStatusesTest extends TestCase
{
    /** @test */
    public function plugin_registers_custom_statuses()
    {
        $awaitingStock = Vocabulary::findByGroupAndCode('order_status', 'awaiting_stock');

        $this->assertNotNull($awaitingStock);
        $this->assertEquals('In attesa di stock', $awaitingStock->getLabel('it'));
        $this->assertEquals('amber', $awaitingStock->getColor());
    }

    /** @test */
    public function custom_status_has_correct_transitions()
    {
        $awaitingStock = Vocabulary::findByGroupAndCode('order_status', 'awaiting_stock');

        $this->assertTrue($awaitingStock->canTransitionTo('processing'));
        $this->assertTrue($awaitingStock->canTransitionTo('cancelled'));
        $this->assertFalse($awaitingStock->canTransitionTo('shipped'));
    }

    /** @test */
    public function quality_check_status_requires_approval()
    {
        $qualityCheck = Vocabulary::findByGroupAndCode('order_status', 'quality_check');

        $this->assertTrue($qualityCheck->getMeta('requires_approval'));
    }
}
```

---

## 📊 Scenario: Multi-Tenant con Vocabolari Custom per Tenant

### Migrazione: Aggiungi `tenant_id` (o `site_id`)

```php
Schema::table('vocabularies', function (Blueprint $table) {
    $table->foreignId('tenant_id')->nullable()->constrained();
});
```

### Plugin per Tenant Specifico

```php
protected function registerTenantSpecificStatuses(): void
{
    $tenantId = config('app.tenant_id'); // o da context

    Vocabulary::createOrUpdate('order_status', 'custom_tenant_status', [
        'labels' => ['en' => 'Custom Status', 'it' => 'Stato custom'],
        'sort_order' => 100,
        'meta' => ['color' => 'pink'],
        'tenant_id' => $tenantId, // ← Specifico per tenant
    ]);
}
```

### Scope nel Model

```php
// In Vocabulary.php
public function scopeForTenant(Builder $query, ?int $tenantId = null): Builder
{
    $tenantId = $tenantId ?? current_tenant_id();

    return $query->where(function ($q) use ($tenantId) {
        $q->where('tenant_id', $tenantId)
          ->orWhereNull('tenant_id'); // Vocabolari globali
    });
}
```

---

## 🚀 Best Practices per Plugin

### ✅ DO

1. **Usa `is_system = false`** per stati del plugin (eliminabili)
2. **Namespace chiaro** per evitare conflitti (es: `warehouse_*`, `custom_*`)
3. **Documenta metadata custom** nel README del plugin
4. **Testa transizioni** tra stati custom e system
5. **Version control** degli stati: aggiungi `plugin_version` in meta

### ❌ DON'T

1. **Non modificare** stati con `is_system = true`
2. **Non usare sort_order** inferiori a 100 per evitare conflitti
3. **Non hardcodare** tenant_id nel plugin (usa context/config)
4. **Non dimenticare** di pulire alla disinstallazione

---

## 🗑️ Disinstallazione Pulita

```php
// In CustomOrderStatusesPlugin.php
public function uninstall(): void
{
    // Rimuovi solo gli stati NON system del plugin
    Vocabulary::where('group', 'order_status')
        ->whereIn('code', ['awaiting_stock', 'in_warehouse', 'quality_check', 'quality_failed'])
        ->where('is_system', false)
        ->delete();

    // Pulisci cache
    \Cache::tags(['vocabularies'])->flush();
}
```

---

## 📝 Checklist Completa Plugin

- [ ] Crea struttura plugin con autoload PSR-4
- [ ] Registra stati in `boot()` del ServiceProvider
- [ ] Usa `is_system = false` per stati custom
- [ ] Definisci `allowed_transitions` coerenti
- [ ] Aggiungi traduzioni per tutte le lingue supportate
- [ ] Testa in ambiente isolato
- [ ] Documenta metadata custom
- [ ] Implementa metodo `uninstall()` pulito
- [ ] Testa integrazione con frontend Inertia
- [ ] Scrivi test automatici

---

## 🎉 Risultato Finale

Con questo approccio:

✅ **Zero modifiche** al core di Cartino
✅ **Plugin completamente autonomo**
✅ **Stati appaiono automaticamente** nelle select
✅ **Traduzioni integrate**
✅ **Disinstallazione pulita**

Il sistema è **production-ready** e **scalabile** per qualsiasi customizzazione!

---

**Documentazione Plugin - Cartino Vocabularies v1.0**
