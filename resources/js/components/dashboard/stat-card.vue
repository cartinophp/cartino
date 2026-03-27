<template>
  <div class="stat-card">
    <div class="stat-card-header">
      <div class="stat-icon" :class="`stat-icon-${icon}`">
        <i :class="['icon', `icon-${icon}`]"></i>
      </div>
      <div class="stat-trend" :class="`stat-trend-${trend}`" v-if="change !== undefined && change !== null">
        <i :class="['icon', trend === 'up' ? 'icon-trending-up' : 'icon-trending-down']"></i>
        <span>{{ Math.abs(change) }}%</span>
      </div>
    </div>

    <div class="stat-content">
      <h3 class="stat-title">{{ title }}</h3>
      <p class="stat-value">{{ value }}</p>
      <div v-if="$slots.subtitle" class="stat-subtitle">
        <slot name="subtitle"></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  value: {
    type: [String, Number],
    required: true,
  },
  change: {
    type: Number,
    default: null,
  },
  trend: {
    type: String,
    default: 'up',
    validator: (value) => ['up', 'down'].includes(value),
  },
  icon: {
    type: String,
    default: 'chart',
  },
})
</script>

<style scoped>
.stat-card {
  background: white;
  border-radius: 0.5rem;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: box-shadow 0.2s;
}

.stat-card:hover {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.stat-card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.stat-icon {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.5rem;
  font-size: 1.5rem;
}

.stat-icon-shopping-bag {
  background-color: #eff6ff;
  color: #3b82f6;
}

.stat-icon-currency-euro {
  background-color: #f0fdf4;
  color: #10b981;
}

.stat-icon-users {
  background-color: #fef3c7;
  color: #f59e0b;
}

.stat-icon-trending-up {
  background-color: #fce7f3;
  color: #ec4899;
}

.stat-icon-chart {
  background-color: #ede9fe;
  color: #8b5cf6;
}

.stat-trend {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.25rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 500;
}

.stat-trend-up {
  background-color: #d1fae5;
  color: #065f46;
}

.stat-trend-down {
  background-color: #fee2e2;
  color: #991b1b;
}

.stat-trend .icon {
  font-size: 1rem;
}

.stat-content {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.stat-title {
  font-size: 0.875rem;
  font-weight: 500;
  color: #6b7280;
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  color: #111827;
  margin: 0;
  line-height: 1;
}

.stat-subtitle {
  font-size: 0.875rem;
  color: #6b7280;
}
</style>
