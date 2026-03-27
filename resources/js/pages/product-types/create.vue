<template>
  <FormPage :page="page" formId="product-type-form" @submit="form.post('/cp/product-types')">
    <Card title="General">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <Input v-model="form.name" label="Name" :error="form.errors.name" placeholder="Product type name" required @input="generateSlug" />
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

defineProps({ page: Object })

const form = useForm({
  name: '', slug: '', description: '', status: 'active',
})

const statusOptions = [
  { value: 'active', label: 'Active' },
  { value: 'inactive', label: 'Inactive' },
]

const generateSlug = () => {
  if (!form.slug) {
    form.slug = form.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')
  }
}
</script>
