<template>
  <div class="products-index">
    <h1>Customers</h1>
    <div class="debug-props">
      <code>{{ JSON.stringify($page.props, null, 2) }}</code>
    </div>
  </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3';
//import {PageHeader} from '@cartino/ui';

const props = defineProps({
  // Add props here based on controller
});
</script>

