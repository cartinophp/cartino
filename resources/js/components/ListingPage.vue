<template>
  <div class="listing-page">
    <PageHeader
      v-if="page"
      :title="page.title"
      :breadcrumbs="page.breadcrumbs"
      :actions="page.actions"
    />

    <div class="listing-container">
      <!-- Filters bar -->
      <div v-if="listing.searchable || listing.filters?.length" class="listing-filters">
        <div class="filters-row">
          <Input
            v-if="listing.searchable"
            v-model="search"
            type="search"
            :placeholder="listing.searchPlaceholder || 'Search...'"
            class="search-input"
            @input="handleSearch"
          />

          <Select
            v-for="filter in listing.filters"
            :key="filter.key"
            v-model="activeFilters[filter.key]"
            :options="filter.options"
            :placeholder="filter.placeholder || filter.label"
            class="filter-select"
            @change="applyFilters"
          />

          <Button
            v-if="hasActiveFilters"
            variant="ghost"
            size="sm"
            @click="clearFilters"
          >
            Clear Filters
          </Button>
        </div>
      </div>

      <!-- Bulk actions bar -->
      <div v-if="selectedRows.length > 0 && listing.bulkActions?.length" class="bulk-actions-bar">
        <span class="bulk-count">{{ selectedRows.length }} selected</span>
        <Button
          v-for="action in listing.bulkActions"
          :key="action.action"
          :variant="action.destructive ? 'destructive' : 'secondary'"
          size="sm"
          @click="handleBulkAction(action)"
        >
          {{ action.label }}
        </Button>
      </div>

      <!-- DataTable -->
      <DataTable
        v-if="data?.data?.length"
        :columns="tableColumns"
        :data="data.data"
        :enable-sorting="true"
        :enable-pagination="true"
        :enable-row-selection="listing.selectable !== false"
        :page-size="listing.perPage || 15"
        :loading="loading"
        hoverable
        @update:sorting="handleSort"
        @update:row-selection="handleSelectionChange"
      >
        <template v-for="(_, name) in $slots" #[name]="slotData">
          <slot :name="name" v-bind="slotData" />
        </template>
      </DataTable>

      <!-- Pagination (server-side) -->
      <div v-if="data?.data?.length && data?.last_page > 1" class="listing-pagination">
        <div class="pagination-info">
          Showing {{ data.from }} to {{ data.to }} of {{ data.total }} results
        </div>
        <div class="pagination-controls">
          <Button
            variant="outline"
            size="sm"
            :disabled="!data.prev_page_url"
            @click="goToPage(data.current_page - 1)"
          >
            Previous
          </Button>
          <span class="page-indicator">
            Page {{ data.current_page }} of {{ data.last_page }}
          </span>
          <Button
            variant="outline"
            size="sm"
            :disabled="!data.next_page_url"
            @click="goToPage(data.current_page + 1)"
          >
            Next
          </Button>
        </div>
      </div>

      <!-- Empty state -->
      <Empty
        v-if="!loading && (!data?.data || data.data.length === 0)"
        :title="listing.empty?.title || 'No results found'"
        :description="listing.empty?.description"
      >
        <template v-if="listing.empty?.action" #action>
          <Button :href="listing.empty.action.url">
            {{ listing.empty.action.label }}
          </Button>
        </template>
        <template v-if="listing.empty?.icon" #icon>
          <Icon :name="listing.empty.icon" size="48" />
        </template>
      </Empty>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  PageHeader,
  DataTable,
  Input,
  Select,
  Button,
  Badge,
  Icon,
  Empty,
} from '@cartino/ui'

const props = defineProps({
  page: Object,
  listing: Object,
  data: Object,
})

const emit = defineEmits(['bulk-action'])

const search = ref('')
const activeFilters = ref({})
const selectedRows = ref([])
const loading = ref(false)

let searchTimeout = null

// Convert listing columns to TanStack column defs
const tableColumns = computed(() => {
  if (!props.listing?.columns) return []
  return props.listing.columns.map(col => ({
    id: col.key,
    accessorKey: col.key,
    header: col.label,
    enableSorting: col.sortable ?? false,
    size: col.width ? parseInt(col.width) : undefined,
    meta: {
      format: col.format,
      component: col.component,
    },
  }))
})

const hasActiveFilters = computed(() => {
  return search.value || Object.values(activeFilters.value).some(v => v)
})

function handleSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => applyFilters(), 300)
}

function applyFilters() {
  loading.value = true
  const params = {}

  if (search.value) {
    params['filter[search]'] = search.value
  }

  for (const [k, v] of Object.entries(activeFilters.value)) {
    if (v) params[`filter[${k}]`] = v
  }

  router.get(window.location.pathname, params, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => { loading.value = false },
  })
}

function clearFilters() {
  search.value = ''
  activeFilters.value = {}
  applyFilters()
}

function handleSort(sorting) {
  if (!sorting?.length) return
  const col = sorting[0]
  const prefix = col.desc ? '-' : ''
  loading.value = true

  router.get(window.location.pathname, { sort: `${prefix}${col.id}` }, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => { loading.value = false },
  })
}

function handleSelectionChange(selection) {
  selectedRows.value = Object.keys(selection).filter(k => selection[k])
}

function goToPage(page) {
  loading.value = true
  router.get(window.location.pathname, { page }, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => { loading.value = false },
  })
}

function handleBulkAction(action) {
  if (selectedRows.value.length === 0) return
  if (action.confirm && !confirm(action.confirm)) return

  emit('bulk-action', {
    action: action.action,
    ids: selectedRows.value,
  })
}
</script>

<style scoped>
.listing-page {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.listing-container {
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.listing-filters {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.filters-row {
  display: flex;
  gap: 0.75rem;
  align-items: center;
  flex-wrap: wrap;
}

.search-input {
  flex: 1;
  min-width: 200px;
}

.filter-select {
  min-width: 160px;
}

.bulk-actions-bar {
  display: flex;
  gap: 0.75rem;
  align-items: center;
  padding: 0.75rem 1.5rem;
  background-color: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.bulk-count {
  font-weight: 500;
  color: #374151;
  margin-right: auto;
}

.listing-pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1.5rem;
  border-top: 1px solid #e5e7eb;
}

.pagination-info {
  font-size: 0.875rem;
  color: #6b7280;
}

.pagination-controls {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.page-indicator {
  font-size: 0.875rem;
  color: #374151;
  padding: 0 0.5rem;
}
</style>
