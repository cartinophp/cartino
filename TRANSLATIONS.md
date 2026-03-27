# Traduzioni Cartino (Statamic-style)

Sistema di traduzioni implementato come **Statamic CMS v6**: le traduzioni vengono iniettate **una sola volta** nel layout Blade tramite `window.CartinoConfig.translations`, poi accessibili in JS senza chiamate API.

## 📦 Architettura

```
PHP Layer (Caricamento)
├─ TranslationComposer → Legge da Laravel translator
├─ View Composer → Inietta $translations nel Blade
└─ Blade (app.blade.php) → window.CartinoConfig.translations = @json($translations)

JS Layer (Utilizzo)
├─ translations.ts → Helper __() che legge da window
├─ use-translations.ts → Composable Vue
└─ Componenti Vue → Usano __() o useTranslations()
```

## 🎯 Come funziona

### 1. PHP: Caricamento (STATAMIC-STYLE)

**View Composer** ([src/Http/View/Composers/TranslationComposer.php](src/Http/View/Composers/TranslationComposer.php)):
```php
protected function getTranslations(): array
{
    $translations = [];
    
    foreach (['cp', 'validation', 'messages'] as $namespace) {
        $key = "cartino::{$namespace}";
        $translation = __($key);
        
        if (is_array($translation)) {
            $translations[$namespace] = $translation;
        }
    }
    
    return $translations;
}
```

**Registrazione** ([src/CartinoServiceProvider.php](src/CartinoServiceProvider.php:189)):
```php
$this->app['view']->composer(
    'cartino::app', 
    \Cartino\Http\View\Composers\TranslationComposer::class
);
```

**Blade Layout** ([resources/views/app.blade.php](resources/views/app.blade.php:17)):
```html
<script>
    window.CartinoConfig = {
        locale: @json(app()->getLocale()),
        translations: @json($translations ?? []),  ← QUI AVVIENE LA MAGIA
        csrf_token: @json(csrf_token()),
        // ...
    };
</script>
```

### 2. JS: Utilizzo

**Helper Vanilla JS** ([resources/js/translations.ts](resources/js/translations.ts)):
```typescript
import { __, __choice } from '@/translations'

// Semplice
__('cp.save')  // 'Salva' (in italiano)

// Con placeholder
__('cp.order_number', { number: '12345' })  // 'Ordine #12345'

// Con pluralizzazione
__choice('cp.items_count', 5)  // '5 elementi'
```

**Composable Vue** ([resources/js/composables/use-translations.ts](resources/js/composables/use-translations.ts)):
```vue
<script setup>
import { useTranslations } from '@/composables/use-translations'

const { __, t, locale } = useTranslations()
</script>

<template>
  <button>{{ __('cp.save') }}</button>
  <h1>{{ t('cp.dashboard') }}</h1>
  <p>Locale: {{ locale }}</p>
</template>
```

## 📁 File Traduzioni

### Struttura
```
resources/lang/
├── en/
│   ├── cp.php         ← Control Panel EN
│   └── validation.php
└── it/
    ├── cp.php         ← Control Panel IT
    └── validation.php
```

### Esempio: resources/lang/it/cp.php
```php
<?php

return [
    // Actions
    'save' => 'Salva',
    'delete' => 'Elimina',
    'edit' => 'Modifica',
    
    // Navigation
    'dashboard' => 'Dashboard',
    'products' => 'Prodotti',
    'orders' => 'Ordini',
    
    // Placeholders
    'order_number' => 'Ordine #:number',
    
    // Pluralization
    'items_count' => ':count elemento|:count elementi',
];
```

## ✨ Vantaggi rispetto a Inertia share

| Approccio | Problema | Soluzione Statamic-style |
|-----------|----------|--------------------------|
| **Inertia share** | Traduzioni in OGNI response (anche partial) | ✅ Caricate UNA VOLTA nel Blade |
| **API fetch** | Chiamata HTTP extra | ✅ Nessuna chiamata, già in window |
| **JSON pesante** | Props Inertia gonfie | ✅ Fuori dalle props, in window globale |

## 🔧 API Completa

### __()
```typescript
__(key: string, replacements?: Record<string, any>): string

__('cp.save')  // 'Salva'
__('cp.order_number', { number: 123 })  // 'Ordine #123'
```

### __choice()
```typescript
__choice(key: string, count: number, replacements?: Record<string, any>): string

__choice('cp.items_count', 1)   // '1 elemento'
__choice('cp.items_count', 10)  // '10 elementi'
```

### hasTranslation()
```typescript
hasTranslation(key: string): boolean

hasTranslation('cp.save')  // true
hasTranslation('cp.nonexistent')  // false
```

### getLocale()
```typescript
getLocale(): string  // 'it', 'en', etc.
```

## 🧪 Testing

### Verificare caricamento in browser:
```javascript
// DevTools Console
window.CartinoConfig.translations

// Output:
{
  cp: {
    save: 'Salva',
    delete: 'Elimina',
    // ...
  },
  validation: { ... }
}
```

### Verificare funzione __():
```javascript
window.CartinoConfig.translations.cp.save  // 'Salva'
```

## 🚀 Aggiungere nuove traduzioni

1. **Aggiungi chiave in PHP:**
   ```php
   // resources/lang/it/cp.php
   'my_new_key' => 'Mia nuova traduzione',
   ```

2. **Usa in Vue:**
   ```vue
   <template>
     <span>{{ __('cp.my_new_key') }}</span>
   </template>
   ```

3. **Ricarica pagina** (non serve rebuild, le traduzioni vengono lette dal file PHP)

## 📝 Note

- ✅ **Nessun share Inertia** - traduzioni in `window`, non nelle props
- ✅ **Caricamento lazy** - solo i namespace usati (cp, validation, etc.)
- ✅ **Cache-friendly** - cambiano solo al reload della pagina
- ✅ **TypeScript support** - tipi completi per autocomplete
- ✅ **Esattamente come Statamic** - stessa architettura, stessi pattern

## 🔗 File Modificati

- [src/Http/View/Composers/TranslationComposer.php](src/Http/View/Composers/TranslationComposer.php) - Composer
- [src/CartinoServiceProvider.php](src/CartinoServiceProvider.php:189) - Registrazione
- [resources/views/app.blade.php](resources/views/app.blade.php:17) - Blade injection
- [resources/js/translations.ts](resources/js/translations.ts) - Helper JS
- [resources/js/composables/use-translations.ts](resources/js/composables/use-translations.ts) - Composable Vue
- [src/Http/Middleware/HandleInertiaRequests.php](src/Http/Middleware/HandleInertiaRequests.php) - Rimosso share

