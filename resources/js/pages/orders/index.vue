<template>
  <ListingPage :page="page" :listing="listing" :data="data" @bulk-action="handleBulkAction">
    <template #cell-status="{ row }">
      <Badge :variant="statusVariant(row.status)">{{ row.status }}</Badge>
    </template>
    <template #cell-payment_status="{ row }">
      <Badge :variant="paymentVariant(row.payment_status)">{{ row.payment_status }}</Badge>
    </template>
    <template #cell-total="{ row }">
      {{ formatCurrency(row.total) }}
    </template>
    <template #cell-actions="{ row }">
      <div style="display:flex;gap:0.5rem">
        <Button variant="ghost" size="sm" :href="`/cp/orders/${row.id}`"><Icon name="eye" size="16" /></Button>
      </div>
    </template>
  </ListingPage>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { Badge, Button, Icon } from '@cartino/ui'
import ListingPage from '@/components/ListingPage.vue'

defineProps({ page: Object, listing: Object, data: Object })

const statusVariant = (s) => ({ pending: 'warning', processing: 'info', shipped: 'info', delivered: 'success', cancelled: 'destructive', refunded: 'secondary' }[s] || 'default')
const paymentVariant = (s) => ({ pending: 'warning', paid: 'success', failed: 'destructive', refunded: 'secondary' }[s] || 'default')
const formatCurrency = (v) => new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR' }).format(v || 0)
const handleBulkAction = ({ action, ids }) => router.post('/cp/orders/bulk', { action, ids })
</script>
