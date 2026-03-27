<template>
  <div class="asset-containers-create">
    <h1>Create Container</h1>

    <div class="debug-props">
      <h2>Props Data:</h2>
      <code>{{ JSON.stringify($page.props, null, 2) }}</code>
    </div>
  </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  page: Object,
  availableDisks: Array,
  presetExtensions: Object,
});
</script>

<style scoped>
.asset-containers-create {
  padding: 2rem;
}

h1 {
  font-size: 2rem;
  margin-bottom: 1rem;
}

h2 {
  font-size: 1.5rem;
  margin: 1rem 0;
}

.debug-props {
  background: #f5f5f5;
  padding: 1rem;
  border-radius: 8px;
  margin-top: 2rem;
}

pre {
  background: #1e1e1e;
  color: #d4d4d4;
  padding: 1rem;
  border-radius: 4px;
  overflow-x: auto;
  font-family: 'Monaco', 'Menlo', 'Courier New', monospace;
  font-size: 0.875rem;
  line-height: 1.5;
}
</style>
