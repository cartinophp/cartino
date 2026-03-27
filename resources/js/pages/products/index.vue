<template>
  <div class="products-page">
    <page-header
      v-if="page"
      :title="page.title"
      :breadcrumbs="page.breadcrumbs"
      :actions="page.actions"
    />

    <div class="products-container">
      <div class="products-filters">
        <div class="filters-row">
          <input-field
            v-model="filters.search"
            type="search"
            placeholder="Search products..."
            class="search-input"
            @input="handleSearch"
          />

          <select-field
            v-model="filters.status"
            :options="statusOptions"
            placeholder="All Statuses"
            class="filter-select"
            @change="handleFilterChange"
          />

          <select-field
            v-model="filters.category"
            :options="categoryOptions"
            placeholder="All Categories"
            class="filter-select"
            @change="handleFilterChange"
          />

          <button-component
            v-if="hasActiveFilters"
            variant="ghost"
            size="sm"
            @click="clearFilters"
          >
            Clear Filters
          </button-component>
        </div>
      </div>

      <data-table
        v-if="data?.data?.length"
        :columns="columns"
        :data="data.data"
        :pagination="paginationData"
        :selected="selectedProducts"
        :loading="loading"
        selectable
        @selection-change="handleSelectionChange"
        @sort="handleSort"
        @page-change="handlePageChange"
      >
        <template #cell-image="{ row }">
          <div class="product-image">
            <img
              v-if="row.image_url"
              :src="row.image_url"
              :alt="row.name"
              class="product-thumbnail"
            />
            <div v-else class="product-placeholder">
              <icon-component name="image" />
            </div>
          </div>
        </template>

        <template #cell-name="{ row }">
          <div class="product-info">
            <a :href="route('cp.products.show', row.id)" class="product-name">
              {{ row.name }}
            </a>
            <p v-if="row.sku" class="product-sku">SKU: {{ row.sku }}</p>
          </div>
        </template>

        <template #cell-status="{ row }">
          <badge-component :variant="getStatusVariant(row.status)">
            {{ row.status }}
          </badge-component>
        </template>

        <template #cell-price="{ row }">
          <span class="product-price">{{ formatCurrency(row.price) }}</span>
        </template>

        <template #cell-stock="{ row }">
          <span
            class="product-stock"
            :class="{ 'stock-low': row.stock_quantity < 10 }"
          >
            {{ row.stock_quantity }}
          </span>
        </template>

        <template #cell-actions="{ row }">
          <div class="product-actions">
            <button-component
              variant="ghost"
              size="sm"
              :href="route('cp.products.edit', row.id)"
            >
              <icon-component name="edit" size="16" />
            </button-component>

            <button-component
              variant="ghost"
              size="sm"
              @click="handleDelete(row.id)"
            >
              <icon-component name="trash" size="16" />
            </button-component>
          </div>
        </template>

        <template #bulk-actions="{ selected }">
          <div v-if="selected.length > 0" class="bulk-actions-bar">
            <span class="bulk-count">{{ selected.length }} selected</span>

            <button-component
              variant="secondary"
              size="sm"
              @click="handleBulkAction('activate')"
            >
              Activate
            </button-component>

            <button-component
              variant="secondary"
              size="sm"
              @click="handleBulkAction('draft')"
            >
              Set as Draft
            </button-component>

            <button-component
              variant="secondary"
              size="sm"
              @click="handleBulkAction('archive')"
            >
              Archive
            </button-component>

            <button-component
              variant="destructive"
              size="sm"
              @click="handleBulkAction('delete')"
            >
              Delete
            </button-component>
          </div>
        </template>
      </data-table>

      <empty-state
        v-else-if="!loading"
        title="No products found"
        description="Get started by creating your first product"
        :action="{
          label: 'Create Product',
          href: route('cp.products.create'),
        }"
      >
        <template #icon>
          <icon-component name="package" size="48" />
        </template>
      </empty-state>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  PageHeader,
  DataTable,
  Input as InputField,
  Select as SelectField,
  Button as ButtonComponent,
  Badge as BadgeComponent,
  Icon as IconComponent,
  Empty as EmptyState,
} from '@cartino/ui'

const props = defineProps({
  page: Object,
  data: Object,
  actions: Array,
})

const filters = ref({
  search: '',
  status: '',
  category: '',
  sort: '',
})

const selectedProducts = ref([])
const loading = ref(false)

const columns = [
  {
    key: 'image',
    label: '',
    sortable: false,
    width: '60px',
  },
  {
    key: 'name',
    label: 'Product',
    sortable: true,
  },
  {
    key: 'sku',
    label: 'SKU',
    sortable: true,
  },
  {
    key: 'status',
    label: 'Status',
    sortable: true,
  },
  {
    key: 'price',
    label: 'Price',
    sortable: true,
  },
  {
    key: 'stock',
    label: 'Stock',
    sortable: true,
  },
  {
    key: 'actions',
    label: '',
    sortable: false,
    width: '100px',
  },
]

const statusOptions = [
  { value: '', label: 'All Statuses' },
  { value: 'active', label: 'Active' },
  { value: 'draft', label: 'Draft' },
  { value: 'archived', label: 'Archived' },
]

const categoryOptions = computed(() => {
  return [
    { value: '', label: 'All Categories' },
    // TODO: Load from API or props
  ]
})

const paginationData = computed(() => {
  if (!props.data) return null

  return {
    current_page: props.data.current_page,
    last_page: props.data.last_page,
    per_page: props.data.per_page,
    total: props.data.total,
    from: props.data.from,
    to: props.data.to,
  }
})

const hasActiveFilters = computed(() => {
  return filters.value.search || filters.value.status || filters.value.category
})

const route = (name, params = {}) => {
  return window.route ? window.route(name, params) : '#'
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('it-IT', {
    style: 'currency',
    currency: 'EUR',
  }).format(value || 0)
}

const getStatusVariant = (status) => {
  const variants = {
    active: 'success',
    draft: 'warning',
    archived: 'secondary',
  }
  return variants[status] || 'default'
}

const handleSearch = () => {
  // Debounced search
  clearTimeout(window.searchTimeout)
  window.searchTimeout = setTimeout(() => {
    applyFilters()
  }, 300)
}

const handleFilterChange = () => {
  applyFilters()
}

const applyFilters = () => {
  loading.value = true

  const params = {}

  if (filters.value.search) {
    params['filter[search]'] = filters.value.search
  }

  if (filters.value.status) {
    params['filter[status]'] = filters.value.status
  }

  if (filters.value.category) {
    params['filter[category]'] = filters.value.category
  }

  router.get(route('cp.products.index'), params, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => {
      loading.value = false
    },
  })
}

const clearFilters = () => {
  filters.value = {
    search: '',
    status: '',
    category: '',
    sort: '',
  }
  applyFilters()
}

const handleSelectionChange = (selected) => {
  selectedProducts.value = selected
}

const handleSort = ({ column, direction }) => {
  filters.value.sort = `${direction === 'desc' ? '-' : ''}${column}`
  applyFilters()
}

const handlePageChange = (page) => {
  loading.value = true

  router.get(
    route('cp.products.index'),
    { page },
    {
      preserveState: true,
      preserveScroll: true,
      onFinish: () => {
        loading.value = false
      },
    }
  )
}

const handleDelete = (id) => {
  if (!confirm('Are you sure you want to delete this product?')) {
    return
  }

  router.delete(route('cp.products.destroy', id), {
    onSuccess: () => {
      // Handle success
    },
  })
}

const handleBulkAction = (action) => {
  if (selectedProducts.value.length === 0) {
    return
  }

  const confirmMessages = {
    delete: 'Are you sure you want to delete the selected products?',
    archive: 'Archive the selected products?',
    activate: 'Activate the selected products?',
    draft: 'Set the selected products as draft?',
  }

  if (confirmMessages[action] && !confirm(confirmMessages[action])) {
    return
  }

  router.post(
    route('cp.products.bulk'),
    {
      action,
      ids: selectedProducts.value.map((p) => p.id),
    },
    {
      onSuccess: () => {
        selectedProducts.value = []
      },
    }
  )
}
</script>

<style scoped>
.products-page {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.products-container {
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.products-filters {
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.filters-row {
  display: flex;
  gap: 1rem;
  align-items: center;
  flex-wrap: wrap;
}

.search-input {
  flex: 1;
  min-width: 250px;
}

.filter-select {
  min-width: 180px;
}

.product-image {
  display: flex;
  align-items: center;
  justify-content: center;
}

.product-thumbnail {
  width: 40px;
  height: 40px;
  object-fit: cover;
  border-radius: 0.375rem;
}

.product-placeholder {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f3f4f6;
  border-radius: 0.375rem;
  color: #9ca3af;
}

.product-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.product-name {
  font-weight: 500;
  color: #111827;
  text-decoration: none;
}

.product-name:hover {
  color: #3b82f6;
}

.product-sku {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
}

.product-price {
  font-weight: 500;
}

.product-stock {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  background-color: #f0fdf4;
  color: #166534;
  font-weight: 500;
}

.product-stock.stock-low {
  background-color: #fef3c7;
  color: #92400e;
}

.product-actions {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.bulk-actions-bar {
  display: flex;
  gap: 1rem;
  align-items: center;
  padding: 1rem 1.5rem;
  background-color: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.bulk-count {
  font-weight: 500;
  color: #374151;
  margin-right: auto;
}
</style>
