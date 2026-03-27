<template>
  <ListingPage :page="page" :listing="listing" :data="data" @bulk-action="handleBulkAction">
    <template #cell-status="{ row }">
      <Badge :variant="row.status === 'published' ? 'success' : 'secondary'">{{ row.status }}</Badge>
    </template>
    <template #cell-actions="{ row }">
      <div style="display:flex;gap:0.5rem">
        <Button variant="ghost" size="sm" :href="`/cp/categories/${row.id}`"><Icon name="eye" size="16" /></Button>
        <Button variant="ghost" size="sm" :href="`/cp/categories/${row.id}/edit`"><Icon name="edit" size="16" /></Button>
      </div>
    </template>
  </ListingPage>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { Badge, Button, Icon } from '@cartino/ui'
import ListingPage from '@/components/ListingPage.vue'

defineProps({ page: Object, listing: Object, data: Object })

const handleBulkAction = ({ action, ids }) => router.post('/cp/categories/bulk', { action, ids })
</script>

