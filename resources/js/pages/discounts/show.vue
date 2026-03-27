<template>
  <ShowPage :page="page">
    <Card title="General">
      <dl class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Name</dt>
          <dd class="text-sm text-gray-900">{{ discount.name }}</dd>
        </div>
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Code</dt>
          <dd class="text-sm font-mono text-gray-900">{{ discount.code }}</dd>
        </div>
        <div class="flex flex-col gap-1 md:col-span-2">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Description</dt>
          <dd class="text-sm text-gray-900">{{ discount.description || '—' }}</dd>
        </div>
      </dl>
    </Card>

    <Card title="Value">
      <dl class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Type</dt>
          <dd class="text-sm text-gray-900">{{ discount.type }}</dd>
        </div>
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Value</dt>
          <dd class="text-sm text-gray-900">{{ discount.type === 'percentage' ? `${discount.value}%` : `€${Number(discount.value).toFixed(2)}` }}</dd>
        </div>
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Min. Order Amount</dt>
          <dd class="text-sm text-gray-900">{{ discount.minimum_order_amount ? `€${Number(discount.minimum_order_amount).toFixed(2)}` : '—' }}</dd>
        </div>
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Max. Discount</dt>
          <dd class="text-sm text-gray-900">{{ discount.maximum_discount_amount ? `€${Number(discount.maximum_discount_amount).toFixed(2)}` : '—' }}</dd>
        </div>
      </dl>
    </Card>

    <Card title="Usage">
      <dl class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Times Used</dt>
          <dd class="text-sm text-gray-900">{{ discount.usage_count || 0 }}</dd>
        </div>
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Usage Limit</dt>
          <dd class="text-sm text-gray-900">{{ discount.usage_limit || 'Unlimited' }}</dd>
        </div>
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Per Customer Limit</dt>
          <dd class="text-sm text-gray-900">{{ discount.usage_limit_per_customer || 'Unlimited' }}</dd>
        </div>
      </dl>
    </Card>

    <template #sidebar>
      <Card title="Status">
        <Badge :variant="discount.is_enabled ? 'success' : 'secondary'">{{ discount.is_enabled ? 'Enabled' : 'Disabled' }}</Badge>
      </Card>
      <Card title="Schedule">
        <div class="flex flex-col gap-2">
          <div class="flex justify-between text-sm"><span class="text-gray-500">Starts</span><strong>{{ discount.starts_at ? new Date(discount.starts_at).toLocaleDateString() : '—' }}</strong></div>
          <div class="flex justify-between text-sm"><span class="text-gray-500">Expires</span><strong>{{ discount.expires_at ? new Date(discount.expires_at).toLocaleDateString() : 'Never' }}</strong></div>
        </div>
      </Card>
    </template>
  </ShowPage>
</template>

<script setup>
import { Card, Badge } from '@cartino/ui'
import ShowPage from '../../components/ShowPage.vue'

defineProps({ page: Object, discount: Object })
</script>
