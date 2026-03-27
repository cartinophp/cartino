<template>
  <div class="flex flex-col gap-6">
    <PageHeader
      v-if="page"
      :title="page.title"
      :breadcrumbs="page.breadcrumbs"
      :actions="page.actions"
    />

    <div class="grid gap-6" :class="$slots.sidebar ? 'lg:grid-cols-[1fr_320px]' : 'grid-cols-1'">
      <div class="flex flex-col gap-6">
        <slot />
      </div>
      <div v-if="$slots.sidebar" class="flex flex-col gap-6">
        <slot name="sidebar" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { PageHeader } from '@cartino/ui'

defineProps({
  page: Object,
})
</script>
