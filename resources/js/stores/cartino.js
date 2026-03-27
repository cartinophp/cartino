import { defineStore } from "pinia";
import { ref, computed } from "vue";

export const useCartinoStore = defineStore("cartino", () => {
  // State
  const collections = ref(new Map());
  const entries = ref(new Map());
  const fields = ref(new Map());
  const currentUser = ref(null);
  const permissions = ref([]);
  const preferences = ref({});
  const loading = ref(false);
  const errors = ref([]);

  // Collections state
  const createCollection = (collection) => {
    collections.value.set(collection.handle, collection);
  };

  const updateCollection = (handle, data) => {
    if (collections.value.has(handle)) {
      const existing = collections.value.get(handle);
      collections.value.set(handle, { ...existing, ...data });
    }
  };

  const deleteCollection = (handle) => {
    collections.value.delete(handle);
  };

  const getCollection = (handle) => {
    return collections.value.get(handle);
  };

  // Entries state
  const createEntry = (entry) => {
    entries.value.set(entry.id, entry);
  };

  const updateEntry = (id, data) => {
    if (entries.value.has(id)) {
      const existing = entries.value.get(id);
      entries.value.set(id, { ...existing, ...data });
    }
  };

  const deleteEntry = (id) => {
    entries.value.delete(id);
  };

  const getEntry = (id) => {
    return entries.value.get(id);
  };

  const getEntriesByCollection = (collectionHandle) => {
    return Array.from(entries.value.values()).filter(
      (entry) => entry.collection === collectionHandle,
    );
  };

  // Fields state
  const createField = (field) => {
    fields.value.set(field.handle, field);
  };

  const updateField = (handle, data) => {
    if (fields.value.has(handle)) {
      const existing = fields.value.get(handle);
      fields.value.set(handle, { ...existing, ...data });
    }
  };

  const deleteField = (handle) => {
    fields.value.delete(handle);
  };

  const getField = (handle) => {
    return fields.value.get(handle);
  };

  // E-commerce specific getters
  const products = computed(() => {
    return getEntriesByCollection("products");
  });

  const customers = computed(() => {
    return getEntriesByCollection("customers");
  });

  const orders = computed(() => {
    return getEntriesByCollection("orders");
  });

  const categories = computed(() => {
    return getEntriesByCollection("categories");
  });

  // Stats computed
  const totalProducts = computed(() => products.value.length);
  const totalCustomers = computed(() => customers.value.length);
  const totalOrders = computed(() => orders.value.length);

  const totalRevenue = computed(() => {
    return orders.value.reduce((total, order) => {
      return total + (order.total || 0);
    }, 0);
  });

  const lowStockProducts = computed(() => {
    return products.value.filter((product) => {
      const inventory = product.inventory || 0;
      const threshold = product.low_stock_threshold || 10;
      return inventory <= threshold;
    });
  });

  const outOfStockProducts = computed(() => {
    return products.value.filter((product) => (product.inventory || 0) <= 0);
  });

  // User & permissions
  const setCurrentUser = (user) => {
    currentUser.value = user;
  };

  const setPermissions = (userPermissions) => {
    permissions.value = userPermissions;
  };

  const hasPermission = (permission) => {
    return permissions.value.includes(permission);
  };

  const canManage = (resource) => {
    return hasPermission(`manage_${resource}`) || hasPermission("super_admin");
  };

  const canView = (resource) => {
    return hasPermission(`view_${resource}`) || canManage(resource);
  };

  const canEdit = (resource) => {
    return hasPermission(`edit_${resource}`) || canManage(resource);
  };

  const canDelete = (resource) => {
    return hasPermission(`delete_${resource}`) || canManage(resource);
  };

  // Preferences
  const setPreference = (key, value) => {
    preferences.value[key] = value;
  };

  const getPreference = (key, defaultValue = null) => {
    return preferences.value[key] || defaultValue;
  };

  // Loading & errors
  const setLoading = (isLoading) => {
    loading.value = isLoading;
  };

  const addError = (error) => {
    errors.value.push({
      id: Date.now() + Math.random(),
      message: error.message || error,
      type: error.type || "error",
      timestamp: new Date(),
    });
  };

  const removeError = (errorId) => {
    const index = errors.value.findIndex((error) => error.id === errorId);
    if (index !== -1) {
      errors.value.splice(index, 1);
    }
  };

  const clearErrors = () => {
    errors.value = [];
  };

  // API actions
  const fetchCollections = async () => {
    try {
      setLoading(true);
      const response = await fetch("/cp/api/collections");
      const data = await response.json();

      data.forEach((collection) => {
        createCollection(collection);
      });
    } catch (error) {
      addError(error);
    } finally {
      setLoading(false);
    }
  };

  const fetchEntries = async (collectionHandle) => {
    try {
      setLoading(true);
      const response = await fetch(
        `/cp/api/collections/${collectionHandle}/entries`,
      );
      const data = await response.json();

      data.forEach((entry) => {
        createEntry(entry);
      });
    } catch (error) {
      addError(error);
    } finally {
      setLoading(false);
    }
  };

  const saveEntry = async (entryData) => {
    try {
      setLoading(true);
      const method = entryData.id ? "PUT" : "POST";
      const url = entryData.id
        ? `/cp/api/entries/${entryData.id}`
        : `/cp/api/collections/${entryData.collection}/entries`;

      const response = await fetch(url, {
        method,
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(entryData),
      });

      const savedEntry = await response.json();

      if (entryData.id) {
        updateEntry(entryData.id, savedEntry);
      } else {
        createEntry(savedEntry);
      }

      return savedEntry;
    } catch (error) {
      addError(error);
      throw error;
    } finally {
      setLoading(false);
    }
  };

  const deleteEntryById = async (entryId) => {
    try {
      setLoading(true);
      await fetch(`/cp/api/entries/${entryId}`, {
        method: "DELETE",
      });

      deleteEntry(entryId);
    } catch (error) {
      addError(error);
      throw error;
    } finally {
      setLoading(false);
    }
  };

  // Search & filtering
  const searchEntries = (query, collectionHandle = null) => {
    let searchEntries = Array.from(entries.value.values());

    if (collectionHandle) {
      searchEntries = searchEntries.filter(
        (entry) => entry.collection === collectionHandle,
      );
    }

    if (!query) {
      return searchEntries;
    }

    const lowercaseQuery = query.toLowerCase();

    return searchEntries.filter((entry) => {
      // Search in title, content, and other searchable fields
      const searchableFields = [
        "title",
        "slug",
        "content",
        "name",
        "email",
        "sku",
      ];

      return searchableFields.some((field) => {
        const value = entry[field];
        return value && value.toString().toLowerCase().includes(lowercaseQuery);
      });
    });
  };

  const filterEntries = (filters, collectionHandle = null) => {
    let filteredEntries = Array.from(entries.value.values());

    if (collectionHandle) {
      filteredEntries = filteredEntries.filter(
        (entry) => entry.collection === collectionHandle,
      );
    }

    Object.entries(filters).forEach(([field, value]) => {
      if (value !== null && value !== undefined && value !== "") {
        filteredEntries = filteredEntries.filter((entry) => {
          const entryValue = entry[field];

          if (Array.isArray(value)) {
            return value.includes(entryValue);
          }

          if (
            typeof value === "object" &&
            value.min !== undefined &&
            value.max !== undefined
          ) {
            return entryValue >= value.min && entryValue <= value.max;
          }

          return entryValue === value;
        });
      }
    });

    return filteredEntries;
  };

  const sortEntries = (entriesList, sortBy, sortDirection = "asc") => {
    return [...entriesList].sort((a, b) => {
      const aValue = a[sortBy];
      const bValue = b[sortBy];

      if (aValue === bValue) return 0;

      const comparison = aValue > bValue ? 1 : -1;
      return sortDirection === "asc" ? comparison : -comparison;
    });
  };

  return {
    // State
    collections: computed(() => Array.from(collections.value.values())),
    entries: computed(() => Array.from(entries.value.values())),
    fields: computed(() => Array.from(fields.value.values())),
    currentUser: computed(() => currentUser.value),
    permissions: computed(() => permissions.value),
    preferences: computed(() => preferences.value),
    loading: computed(() => loading.value),
    errors: computed(() => errors.value),

    // E-commerce computed
    products,
    customers,
    orders,
    categories,
    totalProducts,
    totalCustomers,
    totalOrders,
    totalRevenue,
    lowStockProducts,
    outOfStockProducts,

    // Category actions
    createCollection,
    updateCollection,
    deleteCollection,
    getCollection,

    // Entry actions
    createEntry,
    updateEntry,
    deleteEntry,
    getEntry,
    getEntriesByCollection,

    // Field actions
    createField,
    updateField,
    deleteField,
    getField,

    // User & permissions
    setCurrentUser,
    setPermissions,
    hasPermission,
    canManage,
    canView,
    canEdit,
    canDelete,

    // Preferences
    setPreference,
    getPreference,

    // Loading & errors
    setLoading,
    addError,
    removeError,
    clearErrors,

    // API actions
    fetchCollections,
    fetchEntries,
    saveEntry,
    deleteEntryById,

    // Search & filtering
    searchEntries,
    filterEntries,
    sortEntries,
  };
});
