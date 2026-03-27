<template>
  <div class="product-create-page">
    <page-header
      v-if="page"
      :title="page.title"
      :breadcrumbs="page.breadcrumbs"
      :actions="page.actions"
    />

    <div class="product-form-container">
      <form id="product-form" @submit.prevent="handleSubmit">
        <tabs-component v-if="page?.tabs" :tabs="tabsConfig" v-model="activeTab">
          <!-- General Tab -->
          <template #general>
            <div class="form-section">
              <div class="form-grid">
                <div class="form-field">
                  <label for="name" class="form-label required">Product Name</label>
                  <input-field
                    id="name"
                    v-model="form.name"
                    type="text"
                    placeholder="Enter product name"
                    :error="errors.name"
                    required
                    @input="generateHandle"
                  />
                  <span v-if="errors.name" class="field-error">{{ errors.name }}</span>
                </div>

                <div class="form-field">
                  <label for="handle" class="form-label required">Handle</label>
                  <input-field
                    id="handle"
                    v-model="form.handle"
                    type="text"
                    placeholder="product-handle"
                    :error="errors.handle"
                    required
                  />
                  <span v-if="errors.handle" class="field-error">{{ errors.handle }}</span>
                  <span class="field-hint">URL-friendly identifier</span>
                </div>

                <div class="form-field full-width">
                  <label for="description" class="form-label">Description</label>
                  <textarea-field
                    id="description"
                    v-model="form.description"
                    rows="5"
                    placeholder="Enter product description"
                    :error="errors.description"
                  />
                </div>

                <div class="form-field">
                  <label for="category_id" class="form-label">Category</label>
                  <select-field
                    id="category_id"
                    v-model="form.category_id"
                    :options="categoryOptions"
                    placeholder="Select category"
                    :error="errors.category_id"
                  />
                </div>

                <div class="form-field">
                  <label for="brand_id" class="form-label">Brand</label>
                  <select-field
                    id="brand_id"
                    v-model="form.brand_id"
                    :options="brandOptions"
                    placeholder="Select brand"
                    :error="errors.brand_id"
                  />
                </div>

                <div class="form-field">
                  <label for="price" class="form-label required">Price</label>
                  <input-field
                    id="price"
                    v-model="form.price"
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                    :error="errors.price"
                    required
                  />
                  <span v-if="errors.price" class="field-error">{{ errors.price }}</span>
                </div>

                <div class="form-field">
                  <label for="compare_at_price" class="form-label">Compare at Price</label>
                  <input-field
                    id="compare_at_price"
                    v-model="form.compare_at_price"
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                  />
                  <span class="field-hint">Show a discount</span>
                </div>

                <div class="form-field">
                  <label for="status" class="form-label required">Status</label>
                  <select-field
                    id="status"
                    v-model="form.status"
                    :options="statusOptions"
                    required
                  />
                </div>

                <div class="form-field">
                  <label for="sku" class="form-label">SKU</label>
                  <input-field
                    id="sku"
                    v-model="form.sku"
                    type="text"
                    placeholder="SKU-001"
                  />
                </div>
              </div>
            </div>
          </template>

          <!-- Inventory Tab -->
          <template #inventory>
            <div class="form-section">
              <div class="form-grid">
                <div class="form-field">
                  <label for="stock_quantity" class="form-label">Stock Quantity</label>
                  <input-field
                    id="stock_quantity"
                    v-model="form.stock_quantity"
                    type="number"
                    min="0"
                    placeholder="0"
                  />
                </div>

                <div class="form-field">
                  <label for="low_stock_threshold" class="form-label">Low Stock Threshold</label>
                  <input-field
                    id="low_stock_threshold"
                    v-model="form.low_stock_threshold"
                    type="number"
                    min="0"
                    placeholder="10"
                  />
                  <span class="field-hint">Alert when stock is below this number</span>
                </div>

                <div class="form-field full-width">
                  <switch-field
                    id="track_inventory"
                    v-model="form.track_inventory"
                    label="Track Inventory"
                  />
                </div>

                <div class="form-field full-width">
                  <switch-field
                    id="continue_selling_when_out_of_stock"
                    v-model="form.continue_selling_when_out_of_stock"
                    label="Continue selling when out of stock"
                  />
                </div>
              </div>
            </div>
          </template>

          <!-- Shipping Tab -->
          <template #shipping>
            <div class="form-section">
              <div class="form-grid">
                <div class="form-field full-width">
                  <switch-field
                    id="requires_shipping"
                    v-model="form.requires_shipping"
                    label="This product requires shipping"
                  />
                </div>

                <template v-if="form.requires_shipping">
                  <div class="form-field">
                    <label for="weight" class="form-label">Weight (kg)</label>
                    <input-field
                      id="weight"
                      v-model="form.weight"
                      type="number"
                      step="0.01"
                      min="0"
                      placeholder="0.00"
                    />
                  </div>

                  <div class="form-field">
                    <label for="weight_unit" class="form-label">Weight Unit</label>
                    <select-field
                      id="weight_unit"
                      v-model="form.weight_unit"
                      :options="weightUnitOptions"
                    />
                  </div>

                  <div class="form-field">
                    <label for="length" class="form-label">Length (cm)</label>
                    <input-field
                      id="length"
                      v-model="form.length"
                      type="number"
                      step="0.01"
                      min="0"
                      placeholder="0.00"
                    />
                  </div>

                  <div class="form-field">
                    <label for="width" class="form-label">Width (cm)</label>
                    <input-field
                      id="width"
                      v-model="form.width"
                      type="number"
                      step="0.01"
                      min="0"
                      placeholder="0.00"
                    />
                  </div>

                  <div class="form-field">
                    <label for="height" class="form-label">Height (cm)</label>
                    <input-field
                      id="height"
                      v-model="form.height"
                      type="number"
                      step="0.01"
                      min="0"
                      placeholder="0.00"
                    />
                  </div>
                </template>
              </div>
            </div>
          </template>

          <!-- SEO Tab -->
          <template #seo>
            <div class="form-section">
              <div class="form-grid">
                <div class="form-field full-width">
                  <label for="meta_title" class="form-label">Meta Title</label>
                  <input-field
                    id="meta_title"
                    v-model="form.meta_title"
                    type="text"
                    placeholder="SEO title"
                    maxlength="60"
                  />
                  <span class="field-hint">{{ form.meta_title?.length || 0 }}/60 characters</span>
                </div>

                <div class="form-field full-width">
                  <label for="meta_description" class="form-label">Meta Description</label>
                  <textarea-field
                    id="meta_description"
                    v-model="form.meta_description"
                    rows="3"
                    placeholder="SEO description"
                    maxlength="160"
                  />
                  <span class="field-hint">{{ form.meta_description?.length || 0 }}/160 characters</span>
                </div>

                <div class="form-field full-width">
                  <label for="meta_keywords" class="form-label">Meta Keywords</label>
                  <input-field
                    id="meta_keywords"
                    v-model="form.meta_keywords"
                    type="text"
                    placeholder="keyword1, keyword2, keyword3"
                  />
                  <span class="field-hint">Comma-separated keywords</span>
                </div>
              </div>
            </div>
          </template>

          <!-- Variants Tab -->
          <template #variants>
            <div class="form-section">
              <div class="variants-section">
                <p class="section-description">
                  Variants allow you to offer different versions of this product (e.g., sizes, colors).
                </p>

                <switch-field
                  id="has_variants"
                  v-model="form.has_variants"
                  label="This product has variants"
                />

                <div v-if="form.has_variants" class="variants-content">
                  <!-- Variants management will be implemented separately -->
                  <empty-state
                    title="Variant management"
                    description="Add options like size, color, etc. to create product variants"
                  >
                    <template #icon>
                      <icon-component name="layers" size="48" />
                    </template>
                  </empty-state>
                </div>
              </div>
            </div>
          </template>
        </tabs-component>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  PageHeader,
  Tabs as TabsComponent,
  Input as InputField,
  Textarea as TextareaField,
  Select as SelectField,
  Switch as SwitchField,
  Button as ButtonComponent,
  Icon as IconComponent,
  Empty as EmptyState,
} from '@cartino/ui'

const props = defineProps({
  page: Object,
  schema: Object,
  categories: Array,
  brands: Array,
})

const activeTab = ref('general')
const errors = ref({})
const submitting = ref(false)

const form = ref({
  name: '',
  handle: '',
  description: '',
  category_id: null,
  brand_id: null,
  price: null,
  compare_at_price: null,
  status: 'draft',
  sku: '',
  stock_quantity: 0,
  low_stock_threshold: 10,
  track_inventory: true,
  continue_selling_when_out_of_stock: false,
  requires_shipping: true,
  weight: null,
  weight_unit: 'kg',
  length: null,
  width: null,
  height: null,
  meta_title: '',
  meta_description: '',
  meta_keywords: '',
  has_variants: false,
})

const tabsConfig = computed(() => {
  if (!props.page?.tabs) return []

  return Object.entries(props.page.tabs).map(([key, tab]) => ({
    key,
    label: tab.label,
  }))
})

const categoryOptions = computed(() => {
  return [
    { value: null, label: 'Select category' },
    ...(props.categories || []).map((cat) => ({
      value: cat.id,
      label: cat.name,
    })),
  ]
})

const brandOptions = computed(() => {
  return [
    { value: null, label: 'Select brand' },
    ...(props.brands || []).map((brand) => ({
      value: brand.id,
      label: brand.name,
    })),
  ]
})

const statusOptions = [
  { value: 'active', label: 'Active' },
  { value: 'draft', label: 'Draft' },
  { value: 'archived', label: 'Archived' },
]

const weightUnitOptions = [
  { value: 'kg', label: 'Kilograms (kg)' },
  { value: 'g', label: 'Grams (g)' },
  { value: 'lb', label: 'Pounds (lb)' },
  { value: 'oz', label: 'Ounces (oz)' },
]

const generateHandle = () => {
  if (!form.value.name || form.value.handle) return

  form.value.handle = form.value.name
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
}

const handleSubmit = () => {
  if (submitting.value) return

  submitting.value = true
  errors.value = {}

  router.post(route('cp.products.store'), form.value, {
    onSuccess: (response) => {
      // Handle success - response might contain redirect
    },
    onError: (formErrors) => {
      errors.value = formErrors
      submitting.value = false
    },
    onFinish: () => {
      submitting.value = false
    },
  })
}

const route = (name, params = {}) => {
  return window.route ? window.route(name, params) : '#'
}
</script>

<style scoped>
.product-create-page {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.product-form-container {
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.form-section {
  padding: 2rem;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-field.full-width {
  grid-column: 1 / -1;
}

.form-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
}

.form-label.required::after {
  content: ' *';
  color: #dc2626;
}

.field-error {
  font-size: 0.875rem;
  color: #dc2626;
}

.field-hint {
  font-size: 0.875rem;
  color: #6b7280;
}

.variants-section {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.section-description {
  color: #6b7280;
  font-size: 0.875rem;
  margin: 0;
}

.variants-content {
  margin-top: 1rem;
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }

  .form-field.full-width {
    grid-column: 1;
  }
}
</style>
