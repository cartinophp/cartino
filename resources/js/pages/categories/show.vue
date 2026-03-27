<template>
  <ShowPage :page="page">
    <Card title="General">
      <dl class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Title</dt>
          <dd class="text-sm text-gray-900">{{ collection.title }}</dd>
        </div>
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Slug</dt>
          <dd class="text-sm text-gray-900">{{ collection.slug }}</dd>
        </div>
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Handle</dt>
          <dd class="text-sm text-gray-900">{{ collection.handle || '—' }}</dd>
        </div>
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Type</dt>
          <dd class="text-sm text-gray-900">{{ collection.collection_type || '—' }}</dd>
        </div>
        <div class="flex flex-col gap-1 md:col-span-2">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Description</dt>
          <dd class="text-sm text-gray-900">{{ collection.description || '—' }}</dd>
        </div>
      </dl>
    </Card>

    <Card title="Products" v-if="collection.products?.length">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-200">
            <th class="text-left p-2 text-xs font-medium uppercase text-gray-500">Name</th>
            <th class="text-left p-2 text-xs font-medium uppercase text-gray-500">SKU</th>
            <th class="text-left p-2 text-xs font-medium uppercase text-gray-500">Price</th>
            <th class="text-left p-2 text-xs font-medium uppercase text-gray-500">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in collection.products" :key="p.id" class="border-b border-gray-100">
            <td class="p-2"><a :href="`/cp/products/${p.id}`" class="text-blue-600 hover:underline">{{ p.name }}</a></td>
            <td class="p-2">{{ p.sku || '—' }}</td>
            <td class="p-2">&euro;{{ Number(p.price || 0).toFixed(2) }}</td>
            <td class="p-2"><Badge :variant="p.status === 'active' ? 'success' : 'secondary'">{{ p.status }}</Badge></td>
          </tr>
        </tbody>
      </table>
    </Card>

    <template #sidebar>
      <Card title="Status">
        <Badge :variant="collection.status === 'active' ? 'success' : 'secondary'">{{ collection.status || 'active' }}</Badge>
      </Card>
      <Card title="Parent" v-if="collection.parent">
        <Badge variant="secondary">{{ collection.parent.title }}</Badge>
      </Card>
      <Card title="Stats">
        <div class="flex flex-col gap-2">
          <div class="flex justify-between text-sm"><span class="text-gray-500">Products</span><strong>{{ collection.products_count || collection.products?.length || 0 }}</strong></div>
          <div class="flex justify-between text-sm"><span class="text-gray-500">Created</span><strong>{{ new Date(collection.created_at).toLocaleDateString() }}</strong></div>
        </div>
      </Card>
    </template>
  </ShowPage>
</template>

<script setup>
import { Card, Badge } from '@cartino/ui'
import ShowPage from '../../components/ShowPage.vue'

defineProps({ page: Object, collection: Object })
</script>
