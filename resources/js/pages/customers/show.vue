<template>
  <ShowPage :page="page">
    <Card title="Personal Information">
      <dl class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Name</dt>
          <dd class="text-sm text-gray-900">{{ customer.first_name }} {{ customer.last_name }}</dd>
        </div>
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Email</dt>
          <dd class="text-sm text-gray-900">{{ customer.email }}</dd>
        </div>
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Phone</dt>
          <dd class="text-sm text-gray-900">{{ customer.phone_number || '—' }}</dd>
        </div>
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Date of Birth</dt>
          <dd class="text-sm text-gray-900">{{ customer.date_of_birth || '—' }}</dd>
        </div>
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Gender</dt>
          <dd class="text-sm text-gray-900">{{ customer.gender || '—' }}</dd>
        </div>
        <div class="flex flex-col gap-1">
          <dt class="text-xs font-medium uppercase text-gray-500 tracking-wide">Registered</dt>
          <dd class="text-sm text-gray-900">{{ new Date(customer.created_at).toLocaleDateString() }}</dd>
        </div>
      </dl>
    </Card>

    <Card title="Orders" v-if="customer.orders?.length">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-200">
            <th class="text-left p-2 text-xs font-medium uppercase text-gray-500">Order</th>
            <th class="text-left p-2 text-xs font-medium uppercase text-gray-500">Date</th>
            <th class="text-left p-2 text-xs font-medium uppercase text-gray-500">Status</th>
            <th class="text-left p-2 text-xs font-medium uppercase text-gray-500">Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in customer.orders" :key="order.id" class="border-b border-gray-100">
            <td class="p-2"><a :href="`/cp/orders/${order.id}`" class="text-blue-600 hover:underline">#{{ order.order_number }}</a></td>
            <td class="p-2">{{ new Date(order.created_at).toLocaleDateString() }}</td>
            <td class="p-2"><Badge :variant="order.status === 'delivered' ? 'success' : 'secondary'">{{ order.status }}</Badge></td>
            <td class="p-2">&euro;{{ Number(order.total).toFixed(2) }}</td>
          </tr>
        </tbody>
      </table>
    </Card>

    <Card v-if="customer.addresses?.length" title="Addresses">
      <div class="flex flex-col gap-3">
        <div v-for="addr in customer.addresses" :key="addr.id" class="rounded-md border border-gray-200 p-3">
          <Badge v-if="addr.is_default" variant="info" class="mb-1">Default</Badge>
          <address class="not-italic text-sm leading-relaxed text-gray-700">
            {{ addr.first_name }} {{ addr.last_name }}<br>
            {{ addr.address_line_1 }}<br>
            <span v-if="addr.address_line_2">{{ addr.address_line_2 }}<br></span>
            {{ addr.city }}, {{ addr.postal_code }}<br>
            {{ addr.country_code }}
          </address>
        </div>
      </div>
    </Card>

    <template #sidebar>
      <Card title="Status">
        <Badge :variant="customer.is_enabled ? 'success' : 'secondary'">{{ customer.is_enabled ? 'Active' : 'Inactive' }}</Badge>
      </Card>
      <Card title="Customer Group" v-if="customer.customer_group">
        <Badge variant="secondary">{{ customer.customer_group.name }}</Badge>
      </Card>
      <Card title="Stats">
        <div class="flex flex-col gap-2">
          <div class="flex justify-between text-sm"><span class="text-gray-500">Orders</span><strong>{{ customer.orders_count || customer.orders?.length || 0 }}</strong></div>
          <div class="flex justify-between text-sm"><span class="text-gray-500">Fidelity Points</span><strong>{{ customer.fidelity_card?.points || 0 }}</strong></div>
        </div>
      </Card>
    </template>
  </ShowPage>
</template>

<script setup>
import { Card, Badge } from '@cartino/ui'
import ShowPage from '../../components/ShowPage.vue'

defineProps({ page: Object, customer: Object })
</script>
