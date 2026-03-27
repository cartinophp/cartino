<template>
  <FormPage :page="page" formId="resource-form" @submit="form.post('/cp/navigations')">
    <Card title="General">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <Input v-model="form.title" label="Title" :error="form.errors.title" placeholder="Navigation name" required @input="generateHandle" />
        <Input v-model="form.handle" label="Handle" :error="form.errors.handle" placeholder="main-menu" required />
        <div class="md:col-span-2 flex flex-col gap-1.5">
          <label class="text-sm font-medium text-gray-700">Description</label>
          <Textarea v-model="form.description" rows="3" placeholder="Navigation description" />
        </div>
      </div>
    </Card>

    <template #sidebar>
      <Card title="Status">
        <Switch v-model="form.is_active" label="Active" />
      </Card>
    </template>
  </FormPage>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { Card, Input, Textarea, Switch } from '@cartino/ui'
import FormPage from '../../components/FormPage.vue'

defineProps({ page: Object })

const form = useForm({
  title: '', handle: '', description: '', is_active: true,
})

const generateHandle = () => {
  if (!form.handle) {
    form.handle = form.title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')
  }
}
</script>
