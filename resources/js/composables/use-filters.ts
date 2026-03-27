import { ref, watch, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { debounce } from 'lodash-es'

export interface FilterOptions {
  debounce?: number
  preserveState?: boolean
  preserveScroll?: boolean
  replace?: boolean
  only?: string[]
}

export interface Filters {
  [key: string]: any
}

/**
 * Composable for handling filters with Inertia.js
 */
export function useFilters(
  initialFilters: Filters = {},
  routeName: string,
  options: FilterOptions = {}
) {
  const {
    debounce: debounceMs = 300,
    preserveState = true,
    preserveScroll = true,
    replace = false,
    only = [],
  } = options

  const filters = ref<Filters>({ ...initialFilters })
  const loading = ref(false)

  const hasActiveFilters = computed(() => {
    return Object.values(filters.value).some((value) => {
      if (Array.isArray(value)) {
        return value.length > 0
      }
      return value !== null && value !== undefined && value !== ''
    })
  })

  const buildQueryParams = (): Record<string, any> => {
    const params: Record<string, any> = {}

    Object.entries(filters.value).forEach(([key, value]) => {
      if (value === null || value === undefined || value === '') {
        return
      }

      if (Array.isArray(value) && value.length === 0) {
        return
      }

      // Use Laravel query builder format: filter[key]=value
      params[`filter[${key}]`] = value
    })

    return params
  }

  const applyFilters = () => {
    loading.value = true

    const params = buildQueryParams()

    const visitOptions: any = {
      preserveState,
      preserveScroll,
      replace,
      onFinish: () => {
        loading.value = false
      },
    }

    if (only.length > 0) {
      visitOptions.only = only
    }

    router.get(window.route(routeName), params, visitOptions)
  }

  const debouncedApplyFilters = debounce(applyFilters, debounceMs)

  const setFilter = (key: string, value: any, immediate = false) => {
    filters.value[key] = value

    if (immediate) {
      applyFilters()
    } else {
      debouncedApplyFilters()
    }
  }

  const clearFilter = (key: string) => {
    filters.value[key] = Array.isArray(filters.value[key]) ? [] : ''
    applyFilters()
  }

  const clearAllFilters = () => {
    Object.keys(filters.value).forEach((key) => {
      filters.value[key] = Array.isArray(filters.value[key]) ? [] : ''
    })
    applyFilters()
  }

  const resetFilters = () => {
    filters.value = { ...initialFilters }
    applyFilters()
  }

  // Auto-apply filters on change (optional)
  const watchFilters = (keys?: string[]) => {
    const keysToWatch = keys || Object.keys(filters.value)

    keysToWatch.forEach((key) => {
      watch(
        () => filters.value[key],
        () => {
          debouncedApplyFilters()
        }
      )
    })
  }

  return {
    filters,
    loading,
    hasActiveFilters,
    setFilter,
    clearFilter,
    clearAllFilters,
    resetFilters,
    applyFilters,
    buildQueryParams,
    watchFilters,
  }
}
