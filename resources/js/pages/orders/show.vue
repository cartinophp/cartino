<template>
  <ShowPage :page="page">
    <Card title="Order Details">
      <dl class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Order Number</dt>
          <dd class="text-sm text-gray-900">#{{ order.order_number }}</dd>
        </div>
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Date</dt>
          <dd class="text-sm text-gray-900">{{ new Date(order.created_at).toLocaleDateString() }}</dd>
        </div>
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Customer</dt>
          <dd class="text-sm text-gray-900">{{ order.customer?.first_name }} {{ order.customer?.last_name }}</dd>
        </div>
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Email</dt>
          <dd class="text-sm text-gray-900">{{ order.customer?.email || '—' }}</dd>
        </div>
      </dl>
    </Card>

    <Card title="Items">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-200">
            <th class="text-left p-2 text-xs font-medium uppercase text-gray-500">Product</th>
            <th class="text-left p-2 text-xs font-medium uppercase text-gray-500">Qty</th>
            <th class="text-left p-2 text-xs font-medium uppercase text-gray-500">Unit Price</th>
            <th class="text-left p-2 text-xs font-medium uppercase text-gray-500">Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in order.items" :key="item.id" class="border-b border-gray-100">
            <td class="p-2">{{ item.product?.name || item.title }}</td>
            <td class="p-2">{{ item.quantity }}</td>
            <td class="p-2">{{ formatCurrency(item.unit_price) }}</td>
            <td class="p-2">{{ formatCurrency(item.quantity * item.unit_price) }}</td>
          </tr>
        </tbody>
      </table>
    </Card>

    <Card title="Totals">
      <dl class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="flex flex-col gap-1"><dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Subtotal</dt><dd class="text-sm text-gray-900">{{ formatCurrency(order.subtotal) }}</dd></div>
        <div class="flex flex-col gap-1"><dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Tax</dt><dd class="text-sm text-gray-900">{{ formatCurrency(order.tax_total) }}</dd></div>
        <div class="flex flex-col gap-1"><dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Shipping</dt><dd class="text-sm text-gray-900">{{ formatCurrency(order.shipping_total) }}</dd></div>
        <div class="flex flex-col gap-1"><dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Discount</dt><dd class="text-sm text-gray-900">{{ formatCurrency(order.discount_total) }}</dd></div>
        <div class="flex flex-col gap-1"><dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Total</dt><dd class="text-lg font-bold text-gray-900">{{ formatCurrency(order.total) }}</dd></div>
      </dl>
    </Card>

    <template #sidebar>
      <Card title="Status">
        <div class="flex flex-col gap-3">
          <div class="flex justify-between items-center">
            <span class="text-sm text-gray-500">Order</span>
            <Badge :variant="statusVariant(order.status)">{{ order.status }}</Badge>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm text-gray-500">Payment</span>
            <Badge :variant="paymentVariant(order.payment_status)">{{ order.payment_status }}</Badge>
          </div>
        </div>
      </Card>
      <Card title="Shipping Address" v-if="order.shipping_address">
        <address class="not-italic text-sm leading-relaxed text-gray-700">
          {{ order.shipping_address.first_name }} {{ order.shipping_address.last_name }}<br>
          {{ order.shipping_address.address_line_1 }}<br>
          <span v-if="order.shipping_address.address_line_2">{{ order.shipping_address.address_line_2 }}<br></span>
          {{ order.shipping_address.city }}, {{ order.shipping_address.postal_code }}<br>
          {{ order.shipping_address.country_code }}
        </address>
      </Card>
      <Card title="Notes" v-if="order.notes">
        <p class="text-sm text-gray-700 m-0">{{ order.notes }}</p>
      </Card>
    </template>
  </ShowPage>
</template>

<script setup>
import { Card, Badge } from '@cartino/ui'
import ShowPage from '../../components/ShowPage.vue'

defineProps({ page: Object, order: Object })

const formatCurrency = (v) => v != null ? `€${Number(v).toFixed(2)}` : '€0.00'
const statusVariant = (s) => ({ pending: 'warning', processing: 'info', shipped: 'info', delivered: 'success', cancelled: 'destructive', refunded: 'secondary' }[s] || 'secondary')
const paymentVariant = (s) => ({ paid: 'success', pending: 'warning', failed: 'destructive', refunded: 'secondary' }[s] || 'secondary')
</script>

