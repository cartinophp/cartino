<template>
  <ListingPage
    :page="page"
    :listing="listing"
    :data="data"
    @bulk-action="handleBulkAction"
  >
    <template #cell-image="{ row }">
      <div class="product-image">
        <img v-if="row.image_url" :src="row.image_url" :alt="row.name" class="product-thumbnail" />
        <div v-else class="product-placeholder">
          <Icon name="image" />
        </div>
      </div>
    </template>

    <template #cell-name="{ row }">
      <div class="product-info">
        <a :href="`/cp/products/${row.id}`" class="product-name">{{ row.name }}</a>
        <p v-if="row.sku" class="product-sku">SKU: {{ row.sku }}</p>
      </div>
    </template>

    <template #cell-status="{ row }">
      <Badge :variant="getStatusVariant(row.status)">{{ row.status }}</Badge>
    </template>

    <template #cell-price="{ row }">
      <span class="product-price">{{ formatCurrency(row.price) }}</span>
    </template>

    <template #cell-stock_quantity="{ row }">
      <span class="product-stock" :class="{ 'stock-low': row.stock_quantity < 10 }">
        {{ row.stock_quantity }}
      </span>
    </template>

    <template #cell-actions="{ row }">
      <div class="actions-row">
        <Button variant="ghost" size="sm" :href="`/cp/products/${row.id}/edit`">
          <Icon name="edit" size="16" />
        </Button>
        <Button variant="ghost" size="sm" @click="handleDelete(row.id)">
          <Icon name="trash" size="16" />
        </Button>
      </div>
    </template>
  </ListingPage>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { Badge, Button, Icon } from '@cartino/ui'
import ListingPage from '@/components/ListingPage.vue'

defineProps({ page: Object, listing: Object, data: Object })

const getStatusVariant = (status) => ({ active: 'success', draft: 'warning', archived: 'secondary' }[status] || 'default')

const formatCurrency = (v) => new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR' }).format(v || 0)

const handleDelete = (id) => {
  if (confirm('Are you sure you want to delete this product?')) {
    router.delete(`/cp/products/${id}`)
  }
}

const handleBulkAction = ({ action, ids }) => {
  router.post('/cp/products/bulk', { action, ids })
}
</script>

<style scoped>
.product-image { display: flex; align-items: center; justify-content: center; }
.product-thumbnail { width: 40px; height: 40px; object-fit: cover; border-radius: 0.375rem; }
.product-placeholder { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: #f3f4f6; border-radius: 0.375rem; color: #9ca3af; }
.product-info { display: flex; flex-direction: column; gap: 0.25rem; }
.product-name { font-weight: 500; color: #111827; text-decoration: none; }
.product-name:hover { color: #3b82f6; }
.product-sku { font-size: 0.875rem; color: #6b7280; margin: 0; }
.product-price { font-weight: 500; }
.product-stock { display: inline-block; padding: 0.25rem 0.5rem; border-radius: 0.25rem; background: #f0fdf4; color: #166534; font-weight: 500; }
.product-stock.stock-low { background: #fef3c7; color: #92400e; }
.actions-row { display: flex; gap: 0.5rem; align-items: center; }
</style>
