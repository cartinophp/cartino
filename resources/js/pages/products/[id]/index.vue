<template>
  <ShowPage :page="page">
    <Card title="General">
      <dl class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Name</dt>
          <dd class="text-sm text-gray-900">{{ product.name }}</dd>
        </div>
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">SKU</dt>
          <dd class="text-sm text-gray-900">{{ product.sku || '—' }}</dd>
        </div>
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Brand</dt>
          <dd class="text-sm text-gray-900">{{ product.brand?.name || '—' }}</dd>
        </div>
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Product Type</dt>
          <dd class="text-sm text-gray-900">{{ product.product_type?.name || '—' }}</dd>
        </div>
        <div class="flex flex-col gap-1 md:col-span-2">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Description</dt>
          <dd class="text-sm text-gray-900">{{ product.description || '—' }}</dd>
        </div>
      </dl>
    </Card>

    <Card title="Pricing">
      <dl class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Price</dt>
          <dd class="text-sm text-gray-900">{{ formatCurrency(product.price) }}</dd>
        </div>
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Compare at Price</dt>
          <dd class="text-sm text-gray-900">{{ product.compare_price ? formatCurrency(product.compare_price) : '—' }}</dd>
        </div>
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Cost Price</dt>
          <dd class="text-sm text-gray-900">{{ product.cost_price ? formatCurrency(product.cost_price) : '—' }}</dd>
        </div>
      </dl>
    </Card>

    <Card title="Inventory">
      <dl class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Stock</dt>
          <dd>
            <Badge :variant="product.stock_quantity > 10 ? 'success' : product.stock_quantity > 0 ? 'warning' : 'destructive'">
              {{ product.stock_quantity }}
            </Badge>
          </dd>
        </div>
      </dl>
    </Card>

    <template #sidebar>
      <Card title="Status">
        <Badge :variant="product.status === 'active' ? 'success' : 'secondary'">{{ product.status }}</Badge>
      </Card>
      <Card title="Categories" v-if="product.categories?.length">
        <div class="flex flex-wrap gap-2">
          <Badge v-for="cat in product.categories" :key="cat.id" variant="secondary">{{ cat.name }}</Badge>
        </div>
      </Card>
    </template>
  </ShowPage>
</template>

<script setup>
import { Card, Badge } from '@cartino/ui'
import ShowPage from '../../../components/ShowPage.vue'

defineProps({ page: Object, product: Object })

const formatCurrency = (v) => v != null ? `€${Number(v).toFixed(2)}` : '—'
</script>

