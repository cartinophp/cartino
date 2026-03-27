<template>
  <div class="cp-layout">
    <!-- Navigation Sidebar -->
    nav here

    <!-- Main Content Area -->
    <div class="cp-main">


      <!-- Page Content -->
      <div class="cp-content">
        <slot></slot>
      </div>

    </div>

    <!-- Toast Notifications -->

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useCartinoStore } from "../stores/cartino";


const props = defineProps({
  navigation: Object,
  user: Object,
  sites: Array,
  breadcrumbs: Array,
});

const page = usePage();
const cartinoStore = useCartinoStore();

// State
const globalSearchQuery = ref("");
const showSearchResults = ref(false);
const searchResults = ref([]);
const livePreviewMode = ref(false);
const showLivePreview = ref(false);
const previewDevice = ref("desktop");
const previewUrl = ref("");
const currentSite = ref("default");
const showNotifications = ref(false);
const notifications = ref([]);
const toasts = ref([]);

// Computed
const isMultisite = computed(() => props.sites && props.sites.length > 1);

const unreadCount = computed(() => {
  return notifications.value.filter((n) => !n.read).length;
});

// Methods
const performGlobalSearch = async () => {
  if (!globalSearchQuery.value.trim()) return;

  try {
    const response = await fetch(
      `/cp/api/search?q=${encodeURIComponent(globalSearchQuery.value)}`,
    );
    searchResults.value = await response.json();
    showSearchResults.value = true;
  } catch (error) {
    console.error("Search error:", error);
  }
};

const toggleLivePreview = () => {
  livePreviewMode.value = !livePreviewMode.value;

  if (livePreviewMode.value) {
    previewUrl.value = generatePreviewUrl();
  }
};

const generatePreviewUrl = () => {
  // Generate preview URL based on current page
  const url = page.url;
  if (url.includes("/collections/") && url.includes("/entries/")) {
    return `/preview${url}`;
  }
  return "/";
};

const refreshPreview = () => {
  if (previewFrame.value) {
    previewFrame.value.contentWindow.location.reload();
  }
};

const markAsRead = (notificationId) => {
  const notification = notifications.value.find((n) => n.id === notificationId);
  if (notification) {
    notification.read = true;
  }
};

const markAllAsRead = () => {
  notifications.value.forEach((n) => (n.read = true));
};

const formatTime = (timestamp) => {
  return new Intl.RelativeTimeFormat("en", { numeric: "auto" }).format(
    Math.round((new Date(timestamp) - new Date()) / (1000 * 60 * 60 * 24)),
    "day",
  );
};

const addToast = (message, type = "info") => {
  const toast = {
    id: Date.now() + Math.random(),
    message,
    type,
  };

  toasts.value.push(toast);

  // Auto remove after 5 seconds
  setTimeout(() => {
    removeToast(toast.id);
  }, 5000);
};

const removeToast = (toastId) => {
  const index = toasts.value.findIndex((t) => t.id === toastId);
  if (index > -1) {
    toasts.value.splice(index, 1);
  }
};

const getToastIcon = (type) => {
  const icons = {
    success: "check-circle",
    error: "x-circle",
    warning: "exclamation-triangle",
    info: "information-circle",
  };
  return icons[type] || "information-circle";
};

// Load notifications
const loadNotifications = async () => {
  try {
    const response = await fetch("/cp/api/notifications");
    notifications.value = await response.json();
  } catch (error) {
    console.error("Failed to load notifications:", error);
  }
};

// Click outside handler for dropdowns
const handleClickOutside = (event) => {
  if (!event.target.closest(".global-search")) {
    showSearchResults.value = false;
  }
  if (!event.target.closest(".notifications")) {
    showNotifications.value = false;
  }
};

onMounted(() => {
  loadNotifications();
  document.addEventListener("click", handleClickOutside);

  // Set up live preview detection
  showLivePreview.value = false;
});

// Expose methods for child components
defineExpose({
  addToast,
});
</script>

<style scoped>
.cp-layout {
  display: flex;
  height: 100vh;
  background: #f8fafc;
}

.cp-sidebar {
  flex-shrink: 0;
}

.cp-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.cp-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 2rem;
  background: white;
  border-bottom: 1px solid #e5e7eb;
  min-height: 60px;
}

.breadcrumbs {
  flex: 1;
}

.breadcrumb-list {
  display: flex;
  align-items: center;
  list-style: none;
  margin: 0;
  padding: 0;
  font-size: 0.875rem;
}

.breadcrumb-item {
  display: flex;
  align-items: center;
}

.breadcrumb-link {
  color: #6b7280;
  text-decoration: none;
  transition: color 0.2s;
}

.breadcrumb-link:hover {
  color: #374151;
}

.breadcrumb-current {
  color: #1f2937;
  font-weight: 500;
}

.breadcrumb-separator {
  margin: 0 0.5rem;
  color: #9ca3af;
}

.global-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.global-search {
  position: relative;
}

.search-wrapper {
  display: flex;
  align-items: center;
  background: #f3f4f6;
  border-radius: 0.5rem;
  padding: 0.5rem;
  min-width: 300px;
}

.search-input {
  flex: 1;
  border: none;
  background: none;
  outline: none;
  font-size: 0.875rem;
  padding: 0.25rem 0.5rem;
}

.search-button {
  background: none;
  border: none;
  padding: 0.25rem;
  cursor: pointer;
  color: #6b7280;
}

.search-icon {
  width: 16px;
  height: 16px;
}

.search-results {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  z-index: 50;
  max-height: 300px;
  overflow-y: auto;
}

.search-result {
  border-bottom: 1px solid #f3f4f6;
}

.search-result:last-child {
  border-bottom: none;
}

.result-link {
  display: flex;
  align-items: center;
  padding: 0.75rem;
  text-decoration: none;
  color: inherit;
  transition: background-color 0.2s;
}

.result-link:hover {
  background: #f9fafb;
}

.result-icon {
  width: 24px;
  height: 24px;
  margin-right: 0.75rem;
  color: #6b7280;
}

.result-content {
  flex: 1;
}

.result-title {
  font-weight: 500;
  color: #1f2937;
}

.result-type {
  font-size: 0.75rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.live-preview-toggle {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #f3f4f6;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
}

.live-preview-toggle:hover {
  background: #e5e7eb;
}

.live-preview-toggle.active {
  background: #3b82f6;
  border-color: #3b82f6;
  color: white;
}

.notifications {
  position: relative;
}

.notification-button {
  position: relative;
  background: none;
  border: none;
  padding: 0.5rem;
  cursor: pointer;
  color: #6b7280;
  transition: color 0.2s;
}

.notification-button:hover {
  color: #374151;
}

.notification-icon {
  width: 20px;
  height: 20px;
}

.notification-badge {
  position: absolute;
  top: 0;
  right: 0;
  background: #ef4444;
  color: white;
  font-size: 0.75rem;
  padding: 0.125rem 0.375rem;
  border-radius: 9999px;
  min-width: 1.25rem;
  text-align: center;
  line-height: 1;
}

.notification-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  min-width: 350px;
  max-height: 400px;
  z-index: 50;
}

.notification-header {
  display: flex;
  justify-content: between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid #f3f4f6;
}

.notification-header h3 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
}

.mark-all-read {
  background: none;
  border: none;
  color: #3b82f6;
  font-size: 0.875rem;
  cursor: pointer;
}

.cp-content {
  flex: 1;
  overflow: auto;
  background: #f8fafc;
}

.live-preview-frame {
  width: 50%;
  border-left: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
  background: white;
}

.preview-header {
  display: flex;
  justify-content: between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
  background: #f9fafb;
}

.preview-title {
  font-weight: 600;
  color: #374151;
}

.preview-controls {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.device-selector {
  padding: 0.25rem 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 0.25rem;
  font-size: 0.75rem;
}

.preview-iframe {
  flex: 1;
  border: none;
  width: 100%;
  transition: width 0.3s;
}

.preview-iframe.device-tablet {
  width: 768px;
  margin: 0 auto;
}

.preview-iframe.device-mobile {
  width: 375px;
  margin: 0 auto;
}

.toast-container {
  position: fixed;
  top: 1rem;
  right: 1rem;
  z-index: 100;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.toast {
  display: flex;
  align-items: center;
  padding: 1rem;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  min-width: 300px;
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.toast-success {
  border-left: 4px solid #10b981;
}

.toast-error {
  border-left: 4px solid #ef4444;
}

.toast-warning {
  border-left: 4px solid #f59e0b;
}

.toast-info {
  border-left: 4px solid #3b82f6;
}

.toast-content {
  display: flex;
  align-items: center;
  flex: 1;
}

.toast-icon {
  width: 20px;
  height: 20px;
  margin-right: 0.75rem;
}

.toast-close {
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  padding: 0.25rem;
  margin-left: 1rem;
}
</style>
