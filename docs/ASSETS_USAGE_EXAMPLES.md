# Asset System - Usage Examples

Esempi pratici di utilizzo del sistema Assets per Cartino.

## Indice
1. [Upload Assets](#upload-assets)
2. [Attach Assets ai Models](#attach-assets-ai-models)
3. [Gestione Relazioni](#gestione-relazioni)
4. [Immagini Responsive](#immagini-responsive)
5. [API Endpoints](#api-endpoints)
6. [Frontend Integration](#frontend-integration)

---

## Upload Assets

### Upload Singolo

```php
use Cartino\Services\AssetService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function uploadImage(Request $request, AssetService $assetService)
    {
        $request->validate([
            'image' => 'required|image|max:5120', // 5MB
        ]);

        // Upload asset
        $asset = $assetService->upload(
            file: $request->file('image'),
            container: 'products',
            folder: 'electronics/phones',
            userId: auth()->id()
        );

        return response()->json([
            'asset' => $asset,
            'url' => $asset->url,
            'thumbnail' => $asset->glide([], 'product_card'),
        ]);
    }
}
```

### Upload Multiplo

```php
public function uploadMultiple(Request $request, AssetService $assetService)
{
    $request->validate([
        'images' => 'required|array|min:1|max:10',
        'images.*' => 'image|max:5120',
    ]);

    $assets = [];

    foreach ($request->file('images') as $file) {
        $assets[] = $assetService->upload(
            file: $file,
            container: 'products',
            folder: 'gallery'
        );
    }

    return response()->json([
        'assets' => $assets,
        'count' => count($assets),
    ]);
}
```

---

## Attach Assets ai Models

### Product - Attach Immagini

```php
use Cartino\Models\Product;
use Cartino\Models\Asset;

// Crea prodotto
$product = Product::create([
    'name' => 'iPhone 15 Pro Max',
    'sku' => 'IPHONE-15-PM',
    'price' => 1299.99,
]);

// Carica immagini
$mainImage = $assetService->upload($request->file('main_image'), 'products');
$galleryImages = [];
foreach ($request->file('gallery') as $file) {
    $galleryImages[] = $assetService->upload($file, 'products');
}

// Attach immagine principale
$product->attachAsset($mainImage, 'images', [
    'is_primary' => true,
    'sort_order' => 0,
    'meta' => [
        'alt' => 'iPhone 15 Pro Max front view',
        'title' => 'iPhone 15 Pro Max',
    ],
]);

// Attach gallery
foreach ($galleryImages as $index => $asset) {
    $product->attachAsset($asset, 'gallery', [
        'sort_order' => $index,
    ]);
}
```

### Category - Featured Image

```php
use Cartino\Models\Category;

$category = Category::find(1);

$featuredImage = $assetService->upload($request->file('featured'), 'categories');

$category->attachAsset($featuredImage, 'featured_image', [
    'is_primary' => true,
    'meta' => [
        'alt' => 'Electronics Category',
    ],
]);

// Get featured image URL
$imageUrl = $category->image('square'); // Uses HasAssets trait
// or
$imageUrl = $category->image_url; // Uses accessor
```

### Brand - Logo

```php
use Cartino\Models\Brand;

$brand = Brand::find(1);

$logo = $assetService->upload($request->file('logo'), 'brands');

$brand->attachAsset($logo, 'logo', [
    'is_primary' => true,
]);

// Get logo URL
$logoUrl = $brand->image('logo'); // Custom preset for logos
```

---

## Gestione Relazioni

### Get Assets

```php
$product = Product::with('assets')->find(1);

// Get all images
$images = $product->getAssets('images');

// Get primary image
$primaryImage = $product->getPrimaryAsset('images');

// Get featured assets
$featured = $product->getFeaturedAssets('gallery');

// Get image URLs
$imageUrls = $product->imageUrls('images', 'product_card');

// Get gallery with metadata
$gallery = $product->gallery('gallery', 'product_gallery');
// Returns:
// [
//   ['id' => 1, 'url' => '...', 'alt' => '...', 'title' => '...', 'width' => 800, 'height' => 600],
//   ['id' => 2, 'url' => '...', ...],
// ]
```

### Sync Assets (Replace All)

```php
// Replace all images with new ones
$assetIds = [1, 2, 3, 4, 5];
$product->syncAssets($assetIds, 'images');
```

### Reorder Assets

```php
// Manual reorder
$product->reorderAssets('gallery', [
    5 => 0, // asset_id => sort_order
    3 => 1,
    1 => 2,
    4 => 3,
]);

// Auto reorder (sequential 0, 1, 2, 3...)
$product->reorderAssets('gallery');
```

### Set Primary

```php
$asset = Asset::find(5);

$product->setPrimaryAsset($asset, 'images');
```

### Update Asset Metadata in Pivot

```php
$product->updateAssetMeta(
    asset: $asset,
    meta: [
        'alt' => 'Updated alt text for this product',
        'caption' => 'Special caption',
    ],
    collection: 'images'
);
```

### Detach Assets

```php
// Detach single asset
$product->detachAsset($asset, 'images');

// Detach all from collection
$product->detachAllAssets('gallery');
```

---

## Immagini Responsive

### Glide Presets

```php
$product = Product::find(1);
$image = $product->getPrimaryAsset('images');

// Get URLs with different presets
$thumbnail = $image->glide([], 'xs');           // 150x150
$card = $image->glide([], 'product_card');       // 400x400
$gallery = $image->glide([], 'product_gallery'); // 800x800
$zoom = $image->glide([], 'product_zoom');       // 2000x2000

// Custom parameters
$custom = $image->glide([
    'w' => 500,
    'h' => 500,
    'fit' => 'crop',
    'q' => 95,
]);

// Preset + override params
$highQuality = $image->glide(['q' => 100], 'product_card');
```

### Responsive Images (srcset)

```php
$responsive = $image->responsive();

// Returns:
// [
//   'src' => 'https://cdn.example.com/products/iphone.jpg',
//   'srcset' => 'https://...?w=320 320w, https://...?w=640 640w, ...',
//   'sizes' => '100vw',
// ]

// In Blade:
```
```blade
<img src="{{ $responsive['src'] }}"
     srcset="{{ $responsive['srcset'] }}"
     sizes="{{ $responsive['sizes'] }}"
     alt="{{ $image->alt() }}">
```

### Smart Cropping con Focus Point

```php
// Set focus point (via API or code)
$assetService->setFocusPoint($image, x: 65, y: 35);

// Now all crops will use this focus point
$cropped = $image->glide(['w' => 400, 'h' => 400, 'fit' => 'crop']);
// Automatically uses focus point 65-35
```

---

## API Endpoints

### Upload Asset

```bash
POST /api/assets/upload
Content-Type: multipart/form-data

file: [binary]
container: "products"
folder: "electronics/phones"
meta[alt]: "iPhone 15"
meta[title]: "iPhone 15 Pro"
```

### Attach Asset to Product

```bash
POST /api/products/123/assets

{
  "asset_id": 456,
  "collection": "images",
  "is_primary": true,
  "meta": {
    "alt": "Product specific alt text"
  }
}
```

### Get Product Assets

```bash
GET /api/products/123/assets?collection=images

Response:
{
  "data": [
    {
      "id": 456,
      "path": "products/iphone-15.jpg",
      "url": "https://cdn.example.com/products/iphone-15.jpg",
      "meta": {"alt": "iPhone 15", "title": "iPhone 15 Pro"},
      "pivot": {
        "collection": "images",
        "is_primary": true,
        "sort_order": 0
      }
    }
  ],
  "meta": {
    "collection": "images",
    "total": 1,
    "primary": 456
  }
}
```

### Sync Assets

```bash
PUT /api/products/123/assets/sync

{
  "collection": "gallery",
  "asset_ids": [1, 2, 3, 4, 5]
}
```

### Reorder Assets

```bash
POST /api/products/123/assets/reorder

{
  "collection": "images",
  "order": [
    {"asset_id": 5, "sort_order": 0},
    {"asset_id": 3, "sort_order": 1},
    {"asset_id": 1, "sort_order": 2}
  ]
}
```

### Set Primary

```bash
POST /api/products/123/assets/456/set-primary?collection=images
```

### Update Asset Pivot

```bash
PATCH /api/products/123/assets/456

{
  "collection": "images",
  "is_featured": true,
  "meta": {
    "alt": "Updated alt text"
  }
}
```

### Detach Asset

```bash
DELETE /api/products/123/assets/456?collection=images
```

---

## Frontend Integration

### Vue/React Component Example

```javascript
// ProductImageUpload.vue
<template>
  <div class="image-upload">
    <input
      type="file"
      multiple
      @change="uploadImages"
      accept="image/*"
    />

    <div class="preview-grid">
      <div
        v-for="asset in assets"
        :key="asset.id"
        class="image-card"
      >
        <img
          :src="asset.url + '?preset=product_card'"
          :alt="asset.meta.alt"
        />
        <button @click="setPrimary(asset)">Set Primary</button>
        <button @click="detach(asset)">Remove</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
  productId: Number,
});

const assets = ref([]);

const uploadImages = async (event) => {
  const files = event.target.files;
  const formData = new FormData();

  Array.from(files).forEach(file => {
    formData.append('images[]', file);
  });

  // Upload to assets
  const uploadResponse = await axios.post('/api/assets/upload-multiple', {
    files: files,
    container: 'products',
    folder: `product-${props.productId}`,
  }, {
    headers: { 'Content-Type': 'multipart/form-data' }
  });

  // Attach to product
  const assetIds = uploadResponse.data.data.map(asset => asset.id);

  await axios.post(`/api/products/${props.productId}/assets/bulk`, {
    asset_ids: assetIds,
    collection: 'images',
  });

  // Reload assets
  loadAssets();
};

const loadAssets = async () => {
  const response = await axios.get(
    `/api/products/${props.productId}/assets?collection=images`
  );
  assets.value = response.data.data;
};

const setPrimary = async (asset) => {
  await axios.post(
    `/api/products/${props.productId}/assets/${asset.id}/set-primary`,
    { collection: 'images' }
  );
  loadAssets();
};

const detach = async (asset) => {
  await axios.delete(
    `/api/products/${props.productId}/assets/${asset.id}`,
    { params: { collection: 'images' } }
  );
  loadAssets();
};

// Load on mount
loadAssets();
</script>
```

### Responsive Image Component

```vue
<!-- ResponsiveImage.vue -->
<template>
  <picture v-if="asset">
    <!-- WebP source -->
    <source
      v-if="webpSrcset"
      type="image/webp"
      :srcset="webpSrcset"
      :sizes="sizes"
    />

    <!-- Fallback -->
    <img
      :src="asset.url + glideParams('md')"
      :srcset="srcset"
      :sizes="sizes"
      :alt="asset.meta?.alt || ''"
      :loading="lazy ? 'lazy' : 'eager'"
      class="responsive-image"
    />
  </picture>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  asset: Object,
  preset: String,
  lazy: { type: Boolean, default: true },
  sizes: { type: String, default: '100vw' },
});

const breakpoints = [320, 640, 768, 1024, 1366, 1600];

const glideParams = (size) => {
  if (props.preset) {
    return `?preset=${props.preset}`;
  }
  const widths = {
    xs: 150, sm: 300, md: 600, lg: 1200
  };
  return `?w=${widths[size] || 600}`;
};

const srcset = computed(() => {
  return breakpoints
    .map(w => `${props.asset.url}?w=${w} ${w}w`)
    .join(', ');
});

const webpSrcset = computed(() => {
  return breakpoints
    .map(w => `${props.asset.url}?w=${w}&fm=webp ${w}w`)
    .join(', ');
});
</script>
```

### Usage in Product Page

```vue
<template>
  <div class="product-page">
    <div class="product-gallery">
      <!-- Primary Image -->
      <ResponsiveImage
        :asset="primaryImage"
        preset="product_gallery"
        sizes="(max-width: 768px) 100vw, 50vw"
      />

      <!-- Thumbnails -->
      <div class="thumbnails">
        <img
          v-for="asset in galleryImages"
          :key="asset.id"
          :src="asset.url + '?preset=xs'"
          :alt="asset.meta.alt"
          @click="selectImage(asset)"
        />
      </div>
    </div>

    <div class="product-info">
      <h1>{{ product.name }}</h1>
      <p>{{ product.description }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import ResponsiveImage from './ResponsiveImage.vue';

const props = defineProps({
  productId: Number,
});

const product = ref(null);
const assets = ref([]);

const primaryImage = computed(() =>
  assets.value.find(a => a.pivot.is_primary) || assets.value[0]
);

const galleryImages = computed(() =>
  assets.value.filter(a => a.pivot.collection === 'gallery')
);

onMounted(async () => {
  // Load product
  const productRes = await axios.get(`/api/products/${props.productId}`);
  product.value = productRes.data.data;

  // Load assets
  const assetsRes = await axios.get(
    `/api/products/${props.productId}/assets?collection=images`
  );
  assets.value = assetsRes.data.data;
});
</script>
```

---

## Best Practices

### 1. Eager Loading

```php
// ❌ BAD - N+1 queries
$products = Product::all();
foreach ($products as $product) {
    $image = $product->getPrimaryAsset('images');
}

// ✅ GOOD - 2 queries total
$products = Product::with([
    'assets' => fn($q) => $q
        ->wherePivot('collection', 'images')
        ->wherePivot('is_primary', true)
])->get();
```

### 2. Presets vs Custom Params

```php
// ✅ GOOD - Use presets for common sizes
$thumbnail = $asset->glide([], 'product_card');

// ✅ GOOD - Custom params for one-off cases
$special = $asset->glide(['w' => 450, 'h' => 350, 'fit' => 'crop']);

// ❌ BAD - Don't hardcode params everywhere
$thumb = $asset->glide(['w' => 400, 'h' => 400, 'fit' => 'contain', 'bg' => 'FFFFFF', 'q' => 90]);
```

### 3. Focus Points

```php
// Set focus point when uploading/editing
$assetService->setFocusPoint($asset, x: 65, y: 35);

// Glide will automatically use it for crops
$cropped = $asset->glide([], 'square'); // Uses focus point
```

### 4. CDN URLs

```php
// Assets automatically use CDN if configured
$url = $asset->url; // https://cdn.yourdomain.com/products/image.jpg

// Glide transformations also use CDN
$thumbnail = $asset->glide([], 'product_card');
// https://cdn.yourdomain.com/img/products/image.jpg?preset=product_card
```

---

## Performance Tips

1. **Cache Warming**: Warm cache for frequently used presets after upload
2. **Lazy Loading**: Use `loading="lazy"` for images below the fold
3. **WebP**: Always provide WebP sources for modern browsers
4. **Responsive**: Use srcset for automatic device-appropriate images
5. **CDN**: Enable CDN for all assets in production

---

## Troubleshooting

### Asset non si carica

```php
// Check if asset exists
if (!$asset->exists()) {
    // File deleted from disk
}

// Check container configuration
$container = $asset->containerModel;
if (!$container->canDownload()) {
    // Downloads disabled
}
```

### Transformation non genera

```php
// Check GlideService
$glideService = app(\Cartino\Services\GlideService::class);

// Manual generation
$transformation = $glideService->generate($asset, ['w' => 400]);

// Check cache
$cached = $asset->transformations()->where('preset', 'product_card')->first();
```

---

Per ulteriori dettagli, vedi [ASSETS_SYSTEM.md](ASSETS_SYSTEM.md).
