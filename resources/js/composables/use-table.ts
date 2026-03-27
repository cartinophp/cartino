import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

export interface TableColumn {
  key: string
  label: string
  sortable?: boolean
  width?: string
}

export interface SortOptions {
  column: string
  direction: 'asc' | 'desc'
}

export interface TableOptions {
  preserveState?: boolean
  preserveScroll?: boolean
  only?: string[]
}

/**
 * Composable for handling data table functionality
 */
export function useTable<T = any>(
  routeName: string,
  options: TableOptions = {}
) {
  const opts = {
    preserveState: true,
    preserveScroll: true,
    only: [],
    ...options
  }

  const selectedRows = ref<T[]>([])
  const allSelected = ref(false)
  const loading = ref(false)
  const sortColumn = ref<string | null>(null)
  const sortDirection = ref<'asc' | 'desc'>('asc')

  const hasSelection = computed(() => selectedRows.value.length > 0)
  const selectionCount = computed(() => selectedRows.value.length)

  const toggleRow = (row: T) => {
    const index = selectedRows.value.findIndex((r: any) => r.id === (row as any).id)

    if (index > -1) {
      selectedRows.value.splice(index, 1)
    } else {
      selectedRows.value.push(row)
    }

    updateAllSelectedState()
  }

  const toggleAll = (rows: T[]) => {
    if (allSelected.value) {
      selectedRows.value = []
      allSelected.value = false
    } else {
      selectedRows.value = [...rows]
      allSelected.value = true
    }
  }

  const updateAllSelectedState = () => {
    allSelected.value = false
  }

  const clearSelection = () => {
    selectedRows.value = []
    allSelected.value = false
  }

  const isRowSelected = (row: T): boolean => {
    return selectedRows.value.some((r: any) => r.id === (row as any).id)
  }

  const sort = (column: string, additionalParams: Record<string, any> = {}) => {
    if (sortColumn.value === column) {
      sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
      sortColumn.value = column
      sortDirection.value = 'asc'
    }

    applySort(additionalParams)
  }

  const applySort = (additionalParams: Record<string, any> = {}) => {
    if (!sortColumn.value) return

    loading.value = true

    const prefix = sortDirection.value === 'desc' ? '-' : ''
    const sortParam = prefix + sortColumn.value

    const visitOptions: any = {
      preserveState: opts.preserveState,
      preserveScroll: opts.preserveScroll,
      onFinish: () => {
        loading.value = false
      },
    }

    if (opts.only.length > 0) {
      visitOptions.only = opts.only
    }

    router.get(
      window.route(routeName),
      { sort: sortParam, ...additionalParams },
      visitOptions
    )
  }

  const getSortIndicator = (column: string): 'asc' | 'desc' | null => {
    if (sortColumn.value === column) {
      return sortDirection.value
    }
    return null
  }

  const isSortedBy = (column: string): boolean => {
    return sortColumn.value === column
  }

  return {
    selectedRows,
    allSelected,
    hasSelection,
    selectionCount,
    toggleRow,
    toggleAll,
    clearSelection,
    isRowSelected,
    updateAllSelectedState,
    sortColumn,
    sortDirection,
    sort,
    applySort,
    getSortIndicator,
    isSortedBy,
    loading,
  }
}
