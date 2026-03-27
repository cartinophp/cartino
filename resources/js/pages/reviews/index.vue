<template>
  <ListingPage :page="page" :listing="listing" :data="data" @bulk-action="handleBulkAction">
    <template #cell-rating="{ row }">
      <span>{{ '★'.repeat(row.rating) }}{{ '☆'.repeat(5 - row.rating) }}</span>
    </template>
    <template #cell-is_approved="{ row }">
      <Badge :variant="row.is_approved ? 'success' : 'warning'">{{ row.is_approved ? 'Approved' : 'Pending' }}</Badge>
    </template>
    <template #cell-actions="{ row }">
      <div style="display:flex;gap:0.5rem">
        <Button variant="ghost" size="sm" :href="`/cp/reviews/${row.id}`"><Icon name="eye" size="16" /></Button>
      </div>
    </template>
  </ListingPage>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { Badge, Button, Icon } from '@cartino/ui'
import ListingPage from '@/components/ListingPage.vue'

defineProps({ page: Object, listing: Object, data: Object })

const handleBulkAction = ({ action, ids }) => {
  if (action === 'delete') router.post('/cp/reviews/bulk-delete', { ids })
  else router.post('/cp/reviews/bulk-approve', { ids })
}
</script>

