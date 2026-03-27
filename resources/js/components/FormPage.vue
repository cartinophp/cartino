<template>
  <div class="flex flex-col gap-6">
    <PageHeader
      v-if="page"
      :title="page.title"
      :breadcrumbs="page.breadcrumbs"
      :actions="page.actions"
    />

    <form :id="formId" @submit.prevent="$emit('submit')">
      <Tabs v-if="page?.tabs?.length" :tabs="tabItems" v-model="activeTab">
        <template v-for="tab in page.tabs" :key="tab.name" #[tab.name]>
          <slot :name="`tab-${tab.name}`" />
        </template>
      </Tabs>

      <div v-else class="grid gap-6" :class="$slots.sidebar ? 'lg:grid-cols-[1fr_320px]' : 'grid-cols-1'">
        <div class="flex flex-col gap-6">
          <slot />
        </div>
        <div v-if="$slots.sidebar" class="flex flex-col gap-6">
          <slot name="sidebar" />
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { PageHeader, Tabs } from '@cartino/ui'

const props = defineProps({
  page: Object,
  formId: { type: String, default: 'resource-form' },
})

defineEmits(['submit'])

const activeTab = ref(props.page?.tabs?.[0]?.name || '')

const tabItems = computed(() => {
  if (!props.page?.tabs) return []
  return props.page.tabs.map(t => ({ key: t.name, label: t.label }))
})
</script>
