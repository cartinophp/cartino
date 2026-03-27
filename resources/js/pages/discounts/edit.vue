<template>
  <FormPage :page="page" formId="discount-form" @submit="form.put(`/cp/discounts/${discount.id}`)">
    <Card title="General">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <Input v-model="form.name" label="Name" :error="form.errors.name" placeholder="Discount name" required />
        <Input v-model="form.code" label="Code" :error="form.errors.code" placeholder="DISCOUNT10" required />
        <div class="md:col-span-2 flex flex-col gap-1.5">
          <label class="text-sm font-medium text-gray-700">Description</label>
          <Textarea v-model="form.description" rows="3" placeholder="Discount description" />
        </div>
      </div>
    </Card>

    <Card title="Value">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium text-gray-700">Type</label>
          <Select v-model="form.type" :options="typeOptions" />
        </div>
        <Input v-model="form.value" label="Value" :error="form.errors.value" type="number" step="0.01" min="0" placeholder="0.00" required />
        <Input v-model="form.minimum_order_amount" label="Minimum Order Amount" type="number" step="0.01" min="0" placeholder="0.00" />
        <Input v-model="form.maximum_discount_amount" label="Maximum Discount Amount" type="number" step="0.01" min="0" placeholder="0.00" />
      </div>
    </Card>

    <Card title="Limits">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <Input v-model="form.usage_limit" label="Total Usage Limit" type="number" min="0" placeholder="Unlimited" />
        <Input v-model="form.usage_limit_per_customer" label="Per Customer Limit" type="number" min="0" placeholder="Unlimited" />
      </div>
    </Card>

    <Card title="Schedule">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <Input v-model="form.starts_at" label="Start Date" type="datetime-local" />
        <Input v-model="form.expires_at" label="Expiry Date" type="datetime-local" />
      </div>
    </Card>

    <template #sidebar>
      <Card title="Status">
        <Switch v-model="form.is_enabled" label="Enabled" />
      </Card>
    </template>
  </FormPage>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { Card, Input, Textarea, Select, Switch } from '@cartino/ui'
import FormPage from '../../components/FormPage.vue'

const props = defineProps({ page: Object, discount: Object, discount_types: Array })

const form = useForm({
  name: props.discount?.name || '',
  code: props.discount?.code || '',
  description: props.discount?.description || '',
  type: props.discount?.type || 'percentage',
  value: props.discount?.value || null,
  minimum_order_amount: props.discount?.minimum_order_amount || null,
  maximum_discount_amount: props.discount?.maximum_discount_amount || null,
  usage_limit: props.discount?.usage_limit || null,
  usage_limit_per_customer: props.discount?.usage_limit_per_customer || null,
  starts_at: props.discount?.starts_at || '',
  expires_at: props.discount?.expires_at || '',
  is_enabled: props.discount?.is_enabled ?? true,
})

const typeOptions = [
  { value: 'percentage', label: 'Percentage' },
  { value: 'fixed_amount', label: 'Fixed Amount' },
  { value: 'free_shipping', label: 'Free Shipping' },
]
</script>
