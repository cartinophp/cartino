# Asset System - CP Routes

Routes da aggiungere al file `routes/cp.php` per il Control Panel.

## Assets Management

```php
use Cartino\Http\Controllers\Cp\AssetsController;
use Cartino\Http\Controllers\Cp\AssetContainersController;

// Assets
Route::prefix('assets')->name('assets.')->group(function () {
    Route::get('/', [AssetsController::class, 'index'])->name('index');
    Route::get('/create', [AssetsController::class, 'create'])->name('create');
    Route::post('/', [AssetsController::class, 'store'])->name('store');
    Route::get('/{asset}', [AssetsController::class, 'show'])->name('show');
    Route::get('/{asset}/edit', [AssetsController::class, 'edit'])->name('edit');
    Route::put('/{asset}', [AssetsController::class, 'update'])->name('update');
    Route::delete('/{asset}', [AssetsController::class, 'destroy'])->name('destroy');
    Route::post('/bulk-destroy', [AssetsController::class, 'bulkDestroy'])->name('bulk-destroy');
    Route::get('/{asset}/download', [AssetsController::class, 'download'])->name('download');
});

// Asset Containers
Route::prefix('asset-containers')->name('asset-containers.')->group(function () {
    Route::get('/', [AssetContainersController::class, 'index'])->name('index');
    Route::get('/create', [AssetContainersController::class, 'create'])->name('create');
    Route::post('/', [AssetContainersController::class, 'store'])->name('store');
    Route::get('/{assetContainer}', [AssetContainersController::class, 'show'])->name('show');
    Route::get('/{assetContainer}/edit', [AssetContainersController::class, 'edit'])->name('edit');
    Route::put('/{assetContainer}', [AssetContainersController::class, 'update'])->name('update');
    Route::delete('/{assetContainer}', [AssetContainersController::class, 'destroy'])->name('destroy');
});
```

## Pagine Vue Create

### Assets Pages
- ✅ `/resources/js/pages/assets/Index.vue`
- ✅ `/resources/js/pages/assets/Create.vue`
- ✅ `/resources/js/pages/assets/Show.vue`
- ✅ `/resources/js/pages/assets/Edit.vue`

### Asset Containers Pages
- ✅ `/resources/js/pages/asset-containers/Index.vue`
- ✅ `/resources/js/pages/asset-containers/Create.vue`
- ✅ `/resources/js/pages/asset-containers/Show.vue`
- ✅ `/resources/js/pages/asset-containers/Edit.vue`

## Controllers Creati

### CP Controllers
- ✅ `/src/Http/Controllers/CP/AssetsController.php`
- ✅ `/src/Http/Controllers/CP/AssetContainersController.php`

## Features Implementate

### AssetsController (CP)
- `index()` - Lista assets con filtri (container, folder, type, search)
- `create()` - Form upload
- `store()` - Upload multipli
- `show()` - Dettagli asset (transformations, usedBy models)
- `edit()` - Form modifica metadata
- `update()` - Update metadata + focus point + move folder
- `destroy()` - Delete singolo
- `bulkDestroy()` - Delete multipli
- `download()` - Download file

### AssetContainersController (CP)
- `index()` - Lista containers
- `create()` - Form creazione
- `store()` - Crea container
- `show()` - Dettagli container + stats
- `edit()` - Form modifica
- `update()` - Aggiorna container
- `destroy()` - Elimina container (solo se vuoto)

## Props Passate alle Pagine

### assets/Index.vue
```javascript
{
  page: Object,          // Page builder object
  containers: Array,     // Tutti i container
  currentContainer: String,
  folders: Array,        // Folders nel container corrente
  assets: Object,        // Paginated assets
  filters: Object,       // Filtri attivi
  stats: {
    total: Number,
    images: Number,
    videos: Number,
    documents: Number,
    total_size: Number
  }
}
```

### assets/Create.vue
```javascript
{
  page: Object,
  containers: Array,       // Container dove caricare
  selectedContainer: String,
  selectedFolder: String
}
```

### assets/Show.vue
```javascript
{
  page: Object,
  asset: Object,           // Asset completo con relations
  transformations: Array,  // Glide transformations cache
  usedBy: Array,          // Models usando questo asset
  presets: Object         // Config media.presets
}
```

### assets/Edit.vue
```javascript
{
  page: Object,
  asset: Object,
  containers: Array,
  folders: Array         // Per spostare in altra folder
}
```

### asset-containers/Index.vue
```javascript
{
  page: Object,
  containers: Array,      // Con assets_count
  availableDisks: Object  // Config filesystems
}
```

### asset-containers/Create.vue
```javascript
{
  page: Object,
  availableDisks: Array,
  presetExtensions: Object  // Config media.file_types
}
```

### asset-containers/Show.vue
```javascript
{
  page: Object,
  container: Object,
  stats: {
    total_assets: Number,
    total_size: Number,
    images: Number,
    videos: Number,
    documents: Number
  }
}
```

### asset-containers/Edit.vue
```javascript
{
  page: Object,
  container: Object,
  availableDisks: Array,
  presetExtensions: Object
}
```

## Prossimi Passi

1. **Aggiungere le routes** al file `routes/cp.php`
2. **Testare le pagine** navigando nel CP
3. **Implementare UI** sopra il JSON.stringify
4. **Upload drag&drop** component
5. **Asset browser modal** per selezionare assets da altri models (Product, Category, etc.)

## Navigation Menu

Aggiungere al menu CP:

```php
[
    'label' => 'Assets',
    'icon' => 'photo',
    'url' => route('cp.assets.index'),
    'children' => [
        [
            'label' => 'All Assets',
            'url' => route('cp.assets.index'),
        ],
        [
            'label' => 'Upload',
            'url' => route('cp.assets.create'),
        ],
        [
            'label' => 'Containers',
            'url' => route('cp.asset-containers.index'),
        ],
    ],
],
```
