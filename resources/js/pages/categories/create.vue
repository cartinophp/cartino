<template>
  <FormPage :page="page" formId="category-form" @submit="form.post('/cp/categories')">
    <Card title="General">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <Input v-model="form.title" label="Title" :error="form.errors.title" placeholder="Category name" required @input="generateSlug" />
        <Input v-model="form.slug" label="Slug" :error="form.errors.slug" placeholder="category-slug" required />
        <div class="md:col-span-2 flex flex-col gap-1.5">
          <label class="text-sm font-medium text-gray-700">Description</label>
          <Textarea v-model="form.description" rows="4" placeholder="Category description" />
        </div>
      </div>
    </Card>

    <Card title="SEO">
      <div class="flex flex-col gap-5">
        <Input v-model="form.meta_title" label="Meta Title" placeholder="SEO title" maxlength="60" :hint="`${form.meta_title?.length || 0}/60`" />
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
      <Card title="Parent Category">
        <Select v-model="form.parent_id" :options="parentOptions" placeholder="No parent (root)" />
      </Card>
    </template>
  </FormPage>
</template>

<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Card, Input, Textarea, Select } from '@cartino/ui'
import FormPage from '../../components/FormPage.vue'

const props = defineProps({ page: Object, parents: Array })

const form = useForm({
  title: '', slug: '', description: '',
  meta_title: '', meta_description: '',
  status: 'active', parent_id: null,
})

const statusOptions = [
  { value: 'active', label: 'Active' },
  { value: 'inactive', label: 'Inactive' },
]

const parentOptions = computed(() => [
  { value: null, label: 'No parent (root)' },
  ...(props.parents || []).map(p => ({ value: p.id, label: p.title })),
])

const generateSlug = () => {
  if (!form.slug) {
    form.slug = form.title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')
  }
}
</script>
