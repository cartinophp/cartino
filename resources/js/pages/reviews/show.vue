<template>
  <ShowPage :page="page">
    <Card title="Review">
      <dl class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Title</dt>
          <dd class="text-sm text-gray-900">{{ review.title || '—' }}</dd>
        </div>
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Rating</dt>
          <dd class="text-sm text-gray-900">{{ '★'.repeat(review.rating) }}{{ '☆'.repeat(5 - review.rating) }} ({{ review.rating }}/5)</dd>
        </div>
        <div class="flex flex-col gap-1 md:col-span-2">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Content</dt>
          <dd class="text-sm text-gray-900 whitespace-pre-line">{{ review.content || '—' }}</dd>
        </div>
      </dl>
    </Card>

    <Card title="Product & Customer">
      <dl class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Product</dt>
          <dd class="text-sm text-gray-900">
            <a v-if="review.product" :href="`/cp/products/${review.product_id}`" class="text-blue-600 hover:underline">{{ review.product.name }}</a>
            <span v-else>—</span>
          </dd>
        </div>
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Customer</dt>
          <dd class="text-sm text-gray-900">
            <a v-if="review.customer" :href="`/cp/customers/${review.customer_id}`" class="text-blue-600 hover:underline">{{ review.customer.first_name }} {{ review.customer.last_name }}</a>
            <span v-else>—</span>
          </dd>
        </div>
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Date</dt>
          <dd class="text-sm text-gray-900">{{ new Date(review.created_at).toLocaleDateString() }}</dd>
        </div>
      </dl>
    </Card>

    <Card v-if="review.reply_content" title="Reply">
      <p class="text-sm text-gray-900 whitespace-pre-line">{{ review.reply_content }}</p>
      <p v-if="review.replied_at" class="text-xs text-gray-400 mt-2">Replied {{ new Date(review.replied_at).toLocaleDateString() }}</p>
    </Card>

    <template #sidebar>
      <Card title="Status">
        <div class="flex flex-col gap-2">
          <Badge :variant="review.is_approved ? 'success' : 'warning'">{{ review.is_approved ? 'Approved' : 'Pending' }}</Badge>
          <Badge v-if="review.is_verified_purchase" variant="info">Verified Purchase</Badge>
          <Badge v-if="review.is_featured" variant="secondary">Featured</Badge>
        </div>
      </Card>
      <Card title="Helpfulness">
        <div class="flex flex-col gap-2">
          <div class="flex justify-between text-sm"><span class="text-gray-500">Helpful</span><strong>{{ review.helpful_count || 0 }}</strong></div>
          <div class="flex justify-between text-sm"><span class="text-gray-500">Not Helpful</span><strong>{{ review.unhelpful_count || 0 }}</strong></div>
        </div>
      </Card>
    </template>
  </ShowPage>
</template>

<script setup>
import { Card, Badge } from '@cartino/ui'
import ShowPage from '../../components/ShowPage.vue'

defineProps({ page: Object, review: Object })
</script>

