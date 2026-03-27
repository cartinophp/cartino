import { ref, computed } from 'vue'
import axios from 'axios'

const statusesCache = ref(null)
const loading = ref(false)
const error = ref(null)

export function useStatuses() {
  const fetchStatuses = async () => {
    if (statusesCache.value) {
      return statusesCache.value
    }

    loading.value = true
    error.value = null

    try {
      const response = await axios.get('/api/data/statuses')
      statusesCache.value = response.data
      return response.data
    } catch (err) {
      error.value = err.message
      console.error('Error fetching statuses:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  const fetchStatusesByType = async (type) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get(`/api/data/statuses/${type}`)
      return response.data.statuses
    } catch (err) {
      error.value = err.message
      console.error(`Error fetching ${type} statuses:`, err)
      return []
    } finally {
      loading.value = false
    }
  }

  const productStatuses = computed(() => {
    return statusesCache.value?.product || []
  })

  const customerStatuses = computed(() => {
    return statusesCache.value?.customer || []
  })

  const orderStatuses = computed(() => {
    return statusesCache.value?.order || []
  })

  const generalStatuses = computed(() => {
    return statusesCache.value?.general || []
  })

  const getStatusColor = (value, type = 'general') => {
    const statuses = statusesCache.value?.[type] || []
    const status = statuses.find(s => s.value === value)
    return status?.color || 'gray'
  }

  const getStatusLabel = (value, type = 'general') => {
    const statuses = statusesCache.value?.[type] || []
    const status = statuses.find(s => s.value === value)
    return status?.label || value
  }

  return {
    // State
    loading,
    error,
    statusesCache,

    // Methods
    fetchStatuses,
    fetchStatusesByType,
    getStatusColor,
    getStatusLabel,

    // Computed
    productStatuses,
    customerStatuses,
    orderStatuses,
    generalStatuses,
  }
}
