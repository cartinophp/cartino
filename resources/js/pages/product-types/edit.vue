<template>
  <FormPage :page="page" formId="product-type-form" @submit="form.put(`/cp/product-types/${productType.id}`)">
    <Card title="General">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <Input v-model="form.name" label="Name" :error="form.errors.name" placeholder="Product type name" required />
        <Input v-model="form.slug" label="Slug" :error="form.errors.slug" placeholder="product-type-slug" required />
        <div class="md:col-span-2 flex flex-col gap-1.5">
          <label class="text-sm font-medium text-gray-700">Description</label>
          <Textarea v-model="form.description" rows="4" placeholder="Product type description" />
        </div>
      </div>
    </Card>

    <template #sidebar>
      <Card title="Status">
        <Select v-model="form.status" :options="statusOptions" />
      </Card>
    </template>
  </FormPage>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { Card, Input, Textarea, Select } from '@cartino/ui'
import FormPage from '../../components/FormPage.vue'

const props = defineProps({ page: Object, productType: Object })

const form = useForm({
  name: props.productType?.name || '',
  slug: props.productType?.slug || '',
  description: props.productType?.description || '',
  status: props.productType?.status || 'active',
})

const statusOptions = [
  { value: 'active', label: 'Active' },
  { value: 'inactive', label: 'Inactive' },
]
</script>
