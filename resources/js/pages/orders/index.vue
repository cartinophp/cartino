<template>
  <div class="products-index">
    <h1>Orders index</h1>
    <div class="debug-props">
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

