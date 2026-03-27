<template>
  <FormPage :page="page" formId="brand-form" @submit="form.put(`/cp/brands/${brand.id}`)">
    <Card title="General">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <Input v-model="form.name" label="Name" :error="form.errors.name" placeholder="Brand name" required />
        <Input v-model="form.slug" label="Slug" :error="form.errors.slug" placeholder="brand-slug" required />
        <Input v-model="form.website" label="Website" type="url" placeholder="https://..." />
        <div class="md:col-span-2 flex flex-col gap-1.5">
          <label class="text-sm font-medium text-gray-700">Description</label>
          <Textarea v-model="form.description" rows="4" placeholder="Brand description" />
        </div>
      </div>
    </Card>

    <Card title="SEO">
      <div class="flex flex-col gap-5">
        <div class="flex flex-col gap-1.5">
          <Input v-model="form.meta_title" label="Meta Title" placeholder="SEO title" maxlength="60" :hint="`${form.meta_title?.length || 0}/60`" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium text-gray-700">Meta Description</label>
          <Textarea v-model="form.meta_description" rows="3" placeholder="SEO description" maxlength="160" />
          <span class="text-xs text-gray-400">{{ form.meta_description?.length || 0 }}/160</span>
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

const props = defineProps({ page: Object, brand: Object })

const form = useForm({
  name: props.brand?.name || '',
  slug: props.brand?.slug || '',
  description: props.brand?.description || '',
  website: props.brand?.website || '',
  meta_title: props.brand?.meta_title || '',
  meta_description: props.brand?.meta_description || '',
  status: props.brand?.status || 'active',
})

const statusOptions = [
  { value: 'active', label: 'Active' },
  { value: 'inactive', label: 'Inactive' },
]
</script>
