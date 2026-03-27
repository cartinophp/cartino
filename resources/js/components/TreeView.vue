<template>
  <div>
    <div v-if="items.length === 0" class="rounded-lg bg-white shadow-sm p-12">
      <Empty :title="emptyTitle" :description="emptyDescription">
        <template v-if="$slots['empty-action']" #action>
          <slot name="empty-action" />
        </template>
        <template #icon><Icon name="git-branch" size="48" /></template>
      </Empty>
    </div>

    <ul v-else class="m-0 p-0 list-none" :class="depth === 0 ? 'rounded-lg bg-white shadow-sm overflow-hidden' : ''">
      <li
        v-for="item in items"
        :key="item.id"
        class="border-b border-gray-100 last:border-b-0"
        :class="{ 'opacity-50': dragId === item.id }"
        :draggable="draggable"
        @dragstart.stop="onDragStart($event, item)"
        @dragover.prevent.stop="onDragOver($event, item)"
        @dragleave.stop="onDragLeave($event)"
        @drop.stop="onDrop($event, item)"
        @dragend="onDragEnd"
      >
        <div
          class="flex items-center gap-1 px-4 py-2.5 transition-colors hover:bg-gray-50"
          :class="{ 'bg-blue-50 border border-dashed border-blue-500 rounded': dropTarget === item.id }"
        >
          <div v-if="draggable" class="cursor-grab active:cursor-grabbing text-gray-400 p-1 flex items-center">
            <Icon name="grip-vertical" size="14" />
          </div>

          <button
            v-if="hasChildren(item)"
            type="button"
            class="bg-transparent border-none p-1 cursor-pointer text-gray-500 hover:text-gray-700 flex items-center"
            @click="toggle(item.id)"
          >
            <Icon :name="expandedIds.has(item.id) ? 'chevron-down' : 'chevron-right'" size="14" />
          </button>
          <span v-else class="w-[22px] shrink-0" />

          <div class="flex-1 cursor-pointer min-w-0" @click="$emit('select', item)">
            <slot name="item" :item="item" :depth="depth">
              <span class="text-sm font-medium text-gray-900">{{ item[labelField] }}</span>
            </slot>
          </div>

          <div class="flex gap-1 opacity-0 transition-opacity group-hover/row:opacity-100">
            <slot name="actions" :item="item">
              <Button v-if="showEdit" variant="ghost" size="sm" @click.stop="$emit('edit', item)">
                <Icon name="pencil" size="14" />
              </Button>
              <Button v-if="showDelete" variant="ghost" size="sm" @click.stop="$emit('delete', item)">
                <Icon name="trash-2" size="14" />
              </Button>
            </slot>
          </div>
        </div>

        <TreeView
          v-if="hasChildren(item) && expandedIds.has(item.id)"
          class="pl-6"
          :items="getChildren(item)"
          :children-field="childrenField"
          :label-field="labelField"
          :depth="depth + 1"
          :draggable="draggable"
          :show-edit="showEdit"
          :show-delete="showDelete"
          :expanded-ids="expandedIds"
          @select="$emit('select', $event)"
          @edit="$emit('edit', $event)"
          @delete="$emit('delete', $event)"
          @reorder="$emit('reorder', $event)"
          @move="$emit('move', $event)"
        >
          <template #item="slotData"><slot name="item" v-bind="slotData" /></template>
          <template #actions="slotData"><slot name="actions" v-bind="slotData" /></template>
        </TreeView>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Button, Icon, Empty } from '@cartino/ui'

const props = defineProps({
  items: { type: Array, default: () => [] },
  childrenField: { type: String, default: 'children' },
  labelField: { type: String, default: 'title' },
  depth: { type: Number, default: 0 },
  draggable: { type: Boolean, default: true },
  showEdit: { type: Boolean, default: true },
  showDelete: { type: Boolean, default: true },
  expandedIds: { type: Set, default: () => new Set() },
  emptyTitle: { type: String, default: 'No items' },
  emptyDescription: { type: String, default: '' },
})

const emit = defineEmits(['select', 'edit', 'delete', 'reorder', 'move'])

const dragId = ref(null)
const dropTarget = ref(null)

function hasChildren(item) {
  const children = item[props.childrenField]
  return Array.isArray(children) && children.length > 0
}

function getChildren(item) {
  return item[props.childrenField] || []
}

function toggle(id) {
  if (props.expandedIds.has(id)) {
    props.expandedIds.delete(id)
  } else {
    props.expandedIds.add(id)
  }
}

function onDragStart(e, item) {
  dragId.value = item.id
  e.dataTransfer.effectAllowed = 'move'
  e.dataTransfer.setData('text/plain', JSON.stringify({ id: item.id }))
}

function onDragOver(e, item) {
  if (dragId.value === item.id) return
  dropTarget.value = item.id
}

function onDragLeave() {
  dropTarget.value = null
}

function onDrop(e, targetItem) {
  dropTarget.value = null
  const data = JSON.parse(e.dataTransfer.getData('text/plain'))
  if (data.id !== targetItem.id) {
    emit('move', { itemId: data.id, targetId: targetItem.id })
  }
}

function onDragEnd() {
  dragId.value = null
  dropTarget.value = null
}
</script>
