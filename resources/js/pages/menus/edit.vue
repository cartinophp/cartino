<template>
  <FormPage :page="page" formId="resource-form" @submit="form.put(`/cp/navigations/${menu.handle}`)">
    <Card title="General">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <Input v-model="form.title" label="Title" :error="form.errors.title" placeholder="Navigation name" required />
        <Input v-model="form.handle" label="Handle" :error="form.errors.handle" placeholder="main-menu" required />
        <div class="md:col-span-2 flex flex-col gap-1.5">
          <label class="text-sm font-medium text-gray-700">Description</label>
          <Textarea v-model="form.description" rows="3" placeholder="Navigation description" />
        </div>
      </div>
    </Card>

    <Card title="Menu Items">
      <TreeView
        :items="menu.items || []"
        children-field="children"
        label-field="title"
        :draggable="true"
        :show-edit="true"
        :show-delete="true"
        empty-title="No menu items"
        empty-description="Add items to this navigation"
        @edit="editItem"
        @delete="deleteItem"
        @move="moveItem"
      />
      <div class="mt-4">
        <Button variant="outline" size="sm" @click="addItem">
          <Icon name="plus" size="14" class="mr-1" /> Add item
        </Button>
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
import { useForm, router } from '@inertiajs/vue3'
import { Card, Input, Textarea, Switch, Button, Icon } from '@cartino/ui'
import FormPage from '../../components/FormPage.vue'
import TreeView from '../../components/TreeView.vue'

const props = defineProps({ page: Object, menu: Object })

const form = useForm({
  title: props.menu?.title || '',
  handle: props.menu?.handle || '',
  description: props.menu?.description || '',
  is_active: props.menu?.is_active ?? true,
})

function addItem() {
  const title = prompt('Item title')
  if (!title) return
  router.post(`/cp/navigations/${props.menu.handle}/items`, {
    title, url: '#', parent_id: null,
  }, { preserveScroll: true })
}

function editItem(item) {
  const title = prompt('Edit item title', item.title)
  if (!title) return
  router.put(`/cp/navigations/${props.menu.handle}/items/${item.id}`, {
    title, url: item.url,
  }, { preserveScroll: true })
}

function deleteItem(item) {
  if (!confirm(`Delete "${item.title}"?`)) return
  router.delete(`/cp/navigations/${props.menu.handle}/items/${item.id}`, {
    preserveScroll: true,
  })
}

function moveItem({ itemId, targetId }) {
  router.post(`/cp/navigations/${props.menu.handle}/items/reorder`, {
    item_id: itemId, target_id: targetId,
  }, { preserveScroll: true })
}
</script>
