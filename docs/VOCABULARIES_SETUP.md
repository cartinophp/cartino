# 🚀 Vocabularies - Setup Rapido

Sistema **DB-based** per vocabolari traducibili ed estendibili (stati ordine, pagamenti, ecc.)

## ✅ Setup in 3 Step

### 1️⃣ Esegui Migration & Seeder

```bash
# Migration
php artisan migrate

# Popola con dati default
php artisan db:seed --class=\\Cartino\\Database\\Seeders\\VocabularySeeder
```

### 2️⃣ Registra Middleware Inertia

In `src/CartinoServiceProvider.php` (o Kernel.php):

```php
use Cartino\Http\Middleware\ShareVocabularies;

// Nel gruppo web middleware
protected $middlewareGroups = [
    'web' => [
        // ... altri middleware
        ShareVocabularies::class,
    ],
];
```

### 3️⃣ Registra Service (già fatto se usi CartinoServiceProvider)

```php
use Cartino\Services\VocabularyService;

public function register(): void
{
    $this->app->singleton(VocabularyService::class);
}
```

---

## 🎯 Uso Immediato

### Backend: Controller

```php
use Cartino\Models\Vocabulary;

// Ottieni options per select
$statuses = Vocabulary::getSelectOptions('order_status');

// Risultato:
// [
//   ['value' => 'pending', 'label' => 'In attesa', 'color' => 'orange'],
//   ['value' => 'confirmed', 'label' => 'Confermato', 'color' => 'blue'],
//   ...
// ]
```

### Frontend: Vue/Inertia (automatico!)

```vue
<script setup>
import { usePage } from '@inertiajs/vue3'

const { vocabularies } = usePage().props
// vocabularies.order_status
// vocabularies.payment_status
// ecc.
</script>

<template>
  <select v-model="form.status">
    <option
      v-for="status in vocabularies.order_status"
      :key="status.value"
      :value="status.value"
    >
      {{ status.label }}
    </option>
  </select>
</template>
```

---

## 📚 Documentazione Completa

Vedi [`VOCABULARIES.md`](./VOCABULARIES.md) per:

- Architettura completa
- Come estendere con nuovi vocabolari
- Multi-tenant setup
- Workflow e transizioni
- Best practices
- Esempi completi

---

## 🗂️ File Creati

| File | Descrizione |
|------|-------------|
| [`src/Database/Migrations/2025_12_23_create_vocabularies_table.php`](../src/Database/Migrations/2025_12_23_create_vocabularies_table.php) | Migration tabella vocabularies |
| [`src/Models/Vocabulary.php`](../src/Models/Vocabulary.php) | Model con scope e helper |
| [`src/Database/Seeders/VocabularySeeder.php`](../src/Database/Seeders/VocabularySeeder.php) | Seeder con dati default |
| [`src/Services/VocabularyService.php`](../src/Services/VocabularyService.php) | Service per Inertia |
| [`src/Http/Middleware/ShareVocabularies.php`](../src/Http/Middleware/ShareVocabularies.php) | Middleware Inertia |
| [`src/Models/Order.php`](../src/Models/Order.php) | ⚠️ Modificato con relazioni vocabulary |

---

## 🔄 Prossimi Step Consigliati

1. ✅ **Esegui migration e seeder** (vedi sopra)
2. ✅ **Registra middleware**
3. 🔧 **Aggiorna model esistenti** per usare relazioni vocabulary (vedi esempio Order)
4. 🎨 **Crea componenti Vue riutilizzabili** (StatusBadge, VocabularySelect)
5. 📊 **Admin panel** per gestire vocabularies via UI (TODO)

---

## 🆘 Troubleshooting

### Vocabularies non appaiono in Inertia?

```bash
# 1. Verifica middleware registrato
# 2. Verifica seeder eseguito
php artisan db:seed --class=\\Cartino\\Database\\Seeders\\VocabularySeeder

# 3. Pulisci cache
php artisan cache:clear
```

### Voglio aggiungere un nuovo vocabolario?

```php
use Cartino\Models\Vocabulary;

Vocabulary::createOrUpdate('order_status', 'on_hold', [
    'labels' => ['en' => 'On Hold', 'it' => 'In sospeso'],
    'sort_order' => 25,
    'meta' => ['color' => 'yellow'],
    'is_system' => false,
    'is_active' => true,
]);
```

---

**Sistema pronto all'uso! 🎉**
