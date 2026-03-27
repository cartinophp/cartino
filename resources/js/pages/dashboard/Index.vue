<template>
  <div class="dashboard-container">
    <!-- Stats Grid -->
    <div class="stats-grid">
      <stat-card
        v-if="stats?.total_orders"
        title="Total Orders"
        :value="stats.total_orders.value"
        :change="stats.total_orders.change"
        :trend="stats.total_orders.change >= 0 ? 'up' : 'down'"
        icon="shopping-bag"
      >
        <template #subtitle>
          {{ stats.total_orders.this_month }} this month
        </template>
      </stat-card>

      <stat-card
        v-if="stats?.total_revenue"
        title="Total Revenue"
        :value="stats.total_revenue.formatted"
        :change="stats.total_revenue.change"
        :trend="stats.total_revenue.change >= 0 ? 'up' : 'down'"
        icon="currency-euro"
      >
        <template #subtitle>
          {{ formatCurrency(stats.total_revenue.this_month) }} this month
        </template>
      </stat-card>

      <stat-card
        v-if="stats?.total_customers"
        title="Total Customers"
        :value="stats.total_customers.value"
        :change="stats.total_customers.change"
        :trend="stats.total_customers.change >= 0 ? 'up' : 'down'"
        icon="users"
      >
        <template #subtitle>
          {{ stats.total_customers.this_month }} this month
        </template>
      </stat-card>

      <stat-card
        v-if="stats?.average_order_value"
        title="Average Order"
        :value="stats.average_order_value.formatted"
        icon="trending-up"
      />
    </div>

    <!-- Content Grid -->
    <div class="content-grid">
      <!-- Recent Orders -->
      <div class="card">
        <div class="card-header">
          <h3>Recent Orders</h3>
          <a :href="route('cp.orders.index')" class="link-sm">View all</a>
        </div>

        <div v-if="recent_orders?.length" class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Order</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in recent_orders" :key="order.id">
                <td>
                  <a :href="order.url" class="font-medium">{{ order.number }}</a>
                </td>
                <td>{{ order.customer_name }}</td>
                <td class="font-semibold">{{ order.formatted_total }}</td>
                <td>
                  <span :class="['badge', `badge-${getStatusVariant(order.status)}`]">
                    {{ order.status }}
                  </span>
                </td>
                <td>{{ order.created_at }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="empty-state">
          <p>No recent orders</p>
        </div>
      </div>

      <!-- Low Stock Products -->
      <div class="card">
        <div class="card-header">
          <h3>Low Stock Alert</h3>
        </div>

        <div v-if="low_stock_products?.length" class="product-list">
          <div v-for="product in low_stock_products" :key="product.id" class="product-item">
            <div class="product-info">
              <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="product-image" />
              <div>
                <a :href="product.url" class="product-name">{{ product.name }}</a>
                <p class="product-sku">{{ product.sku }}</p>
              </div>
            </div>
            <div class="stock-badge" :class="{ 'stock-critical': product.stock_quantity < 5 }">
              {{ product.stock_quantity }} left
            </div>
          </div>
        </div>
        <div v-else class="empty-state">
          <p>No low stock products</p>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="card">
        <div class="card-header">
          <h3>Recent Activity</h3>
        </div>

        <div v-if="activities?.length" class="activity-feed">
          <div v-for="activity in activities" :key="activity.id" class="activity-item">
            <div class="activity-icon">
              <i :class="['icon', `icon-${activity.icon}`]"></i>
            </div>
            <div class="activity-content">
              <p class="activity-title">{{ activity.title }}</p>
              <p class="activity-description">{{ activity.description }}</p>
              <span class="activity-time">{{ activity.time }}</span>
            </div>
          </div>
        </div>
        <div v-else class="empty-state">
          <p>No recent activity</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import StatCard from '@/components/dashboard/stat-card.vue'

const props = defineProps({
  page: Object,
  stats: Object,
  charts: Object,
  recent_orders: Array,
  low_stock_products: Array,
  top_products: Array,
  activities: Array,
})

const route = (name, params = {}) => {
  return window.route ? window.route(name, params) : '#'
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('it-IT', {
    style: 'currency',
    currency: 'EUR',
  }).format(value || 0)
}

const getStatusVariant = (status) => {
  const variants = {
    completed: 'success',
    processing: 'info',
    pending: 'warning',
    cancelled: 'danger',
  }
  return variants[status] || 'default'
}
</script>

<style scoped>
.dashboard-container {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1rem;
}

.content-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 1.5rem;
}

.card {
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.card-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header h3 {
  font-size: 1.125rem;
  font-weight: 600;
  margin: 0;
}

.link-sm {
  font-size: 0.875rem;
  color: #3b82f6;
  text-decoration: none;
}

.link-sm:hover {
  text-decoration: underline;
}

.table-container {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th,
.data-table td {
  padding: 0.75rem 1.5rem;
  text-align: left;
}

.data-table thead th {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  color: #6b7280;
  background-color: #f9fafb;
}

.data-table tbody tr {
  border-bottom: 1px solid #e5e7eb;
}

.data-table tbody tr:hover {
  background-color: #f9fafb;
}

.badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
}

.badge-success {
  background-color: #d1fae5;
  color: #065f46;
}

.badge-info {
  background-color: #dbeafe;
  color: #1e40af;
}

.badge-warning {
  background-color: #fef3c7;
  color: #92400e;
}

.badge-danger {
  background-color: #fee2e2;
  color: #991b1b;
}

.product-list {
  padding: 0.5rem;
}

.product-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  border-radius: 0.375rem;
}

.product-item:hover {
  background-color: #f9fafb;
}

.product-info {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.product-image {
  width: 40px;
  height: 40px;
  object-fit: cover;
  border-radius: 0.375rem;
}

.product-name {
  font-weight: 500;
  color: #111827;
  text-decoration: none;
}

.product-name:hover {
  color: #3b82f6;
}

.product-sku {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
}

.stock-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 500;
  background-color: #fef3c7;
  color: #92400e;
}

.stock-critical {
  background-color: #fee2e2;
  color: #991b1b;
}

.activity-feed {
  padding: 0.5rem;
}

.activity-item {
  display: flex;
  gap: 0.75rem;
  padding: 0.75rem;
  border-radius: 0.375rem;
}

.activity-item:hover {
  background-color: #f9fafb;
}

.activity-icon {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background-color: #eff6ff;
  color: #3b82f6;
  flex-shrink: 0;
}

.activity-content {
  flex: 1;
}

.activity-title {
  font-weight: 500;
  margin: 0 0 0.25rem 0;
}

.activity-description {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0 0 0.25rem 0;
}

.activity-time {
  font-size: 0.75rem;
  color: #9ca3af;
}

.empty-state {
  padding: 3rem 1.5rem;
  text-align: center;
  color: #6b7280;
}

@media (max-width: 1024px) {
  .content-grid {
    grid-template-columns: 1fr;
  }
}
</style>
