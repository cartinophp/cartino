<template>
  <div class="flex flex-col gap-6">
    <PageHeader
      v-if="page"
      :title="page.title"
      :breadcrumbs="page.breadcrumbs"
      :actions="page.actions"
    />

    <div v-if="menus?.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <a
        v-for="m in menus"
        :key="m.handle"
        :href="`/cp/navigations/${m.handle}/edit`"
        class="block rounded-lg bg-white shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow"
      >
        <h3 class="text-sm font-semibold text-gray-900">{{ m.title }}</h3>
        <p v-if="m.description" class="text-xs text-gray-500 mt-1">{{ m.description }}</p>
        <div class="flex items-center gap-2 mt-3">
          <Badge variant="secondary">{{ m.handle }}</Badge>
          <Badge v-if="m.items_count" variant="info">{{ m.items_count }} items</Badge>
        </div>
      </a>
    </div>

    <div v-else class="rounded-lg bg-white shadow-sm p-12">
      <Empty title="No navigations" description="Create your first navigation menu">
        <template #icon><Icon name="menu" size="48" /></template>
        <template #action>
          <Button as="a" href="/cp/navigations/create">Add navigation</Button>
        </template>
      </Empty>
    </div>
  </div>
</template>

<script setup>
import { PageHeader, Badge, Button, Icon, Empty } from '@cartino/ui'

defineProps({ page: Object, menus: Array })
</script>
