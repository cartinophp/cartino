<template>
  <ListingPage :page="page" :listing="listing" :data="data">
    <template #cell-thumbnail="{ row }">
      <div class="asset-thumb">
        <img v-if="row.type === 'image'" :src="row.url" :alt="row.name" class="thumb-img" />
        <Icon v-else :name="row.type === 'video' ? 'film' : 'file'" size="24" />
      </div>
    </template>
    <template #cell-size="{ row }">
      {{ formatSize(row.size) }}
    </template>
    <template #cell-actions="{ row }">
      <div style="display:flex;gap:0.5rem">
        <Button variant="ghost" size="sm" :href="`/cp/assets/${row.id}`"><Icon name="eye" size="16" /></Button>
        <Button variant="ghost" size="sm" :href="`/cp/assets/${row.id}/edit`"><Icon name="edit" size="16" /></Button>
      </div>
    </template>
  </ListingPage>
</template>

<script setup>
import { Button, Icon } from '@cartino/ui'
import ListingPage from '@/components/ListingPage.vue'

defineProps({ page: Object, listing: Object, data: Object, containers: Array, currentContainer: String })

const formatSize = (bytes) => {
  if (!bytes) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return `${parseFloat((bytes / Math.pow(k, i)).toFixed(1))} ${sizes[i]}`
}
</script>

<style scoped>
.asset-thumb { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: #f3f4f6; border-radius: 0.375rem; overflow: hidden; }
.thumb-img { width: 100%; height: 100%; object-fit: cover; }
</style>
