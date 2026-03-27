<template>
  <div class="reports-index">
    <h1>Settings</h1>

    <div class="debug-props">
      <h2>Props Data:</h2>
      <code>{{ JSON.stringify($page.props, null, 2) }}</code>
    </div>
  </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  // Add props here based on controller
});
</script>
