import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

export interface PaginationData {
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
  links?: PaginationLink[]
}

export interface PaginationLink {
  url: string | null
  label: string
  active: boolean
}

export interface PaginationOptions {
  preserveState?: boolean
  preserveScroll?: boolean
  replace?: boolean
  only?: string[]
}

/**
 * Composable for handling pagination with Inertia.js
 */
export function usePagination(
  data: PaginationData | null,
  routeName: string,
  options: PaginationOptions = {}
) {
  const {
    preserveState = true,
    preserveScroll = true,
    replace = false,
    only = [],
  } = options

  const loading = ref(false)

  const currentPage = computed(() => data?.current_page || 1)
  const lastPage = computed(() => data?.last_page || 1)
  const perPage = computed(() => data?.per_page || 15)
  const total = computed(() => data?.total || 0)
  const from = computed(() => data?.from || 0)
  const to = computed(() => data?.to || 0)

  const hasPages = computed(() => lastPage.value > 1)
  const hasMorePages = computed(() => currentPage.value < lastPage.value)
  const hasPreviousPage = computed(() => currentPage.value > 1)

  const isFirstPage = computed(() => currentPage.value === 1)
  const isLastPage = computed(() => currentPage.value === lastPage.value)

  const pageNumbers = computed(() => {
    const pages: number[] = []
    const range = 2 // Number of pages to show on each side of current page

    for (let i = 1; i <= lastPage.value; i++) {
      if (
        i === 1 ||
        i === lastPage.value ||
        (i >= currentPage.value - range && i <= currentPage.value + range)
      ) {
        pages.push(i)
      }
    }

    return pages
  })

  const goToPage = (page: number, additionalParams: Record<string, any> = {}) => {
    if (page < 1 || page > lastPage.value || page === currentPage.value) {
      return
    }

    loading.value = true

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

    router.get(
      window.route(routeName),
      { page, ...additionalParams },
      visitOptions
    )
  }

  const nextPage = (additionalParams: Record<string, any> = {}) => {
    if (hasMorePages.value) {
      goToPage(currentPage.value + 1, additionalParams)
    }
  }

  const previousPage = (additionalParams: Record<string, any> = {}) => {
    if (hasPreviousPage.value) {
      goToPage(currentPage.value - 1, additionalParams)
    }
  }

  const firstPage = (additionalParams: Record<string, any> = {}) => {
    if (!isFirstPage.value) {
      goToPage(1, additionalParams)
    }
  }

  const lastPageAction = (additionalParams: Record<string, any> = {}) => {
    if (!isLastPage.value) {
      goToPage(lastPage.value, additionalParams)
    }
  }

  const getPaginationInfo = (): string => {
    if (total.value === 0) {
      return 'No results'
    }

    return `Showing ${from.value} to ${to.value} of ${total.value} results`
  }

  return {
    currentPage,
    lastPage,
    perPage,
    total,
    from,
    to,
    hasPages,
    hasMorePages,
    hasPreviousPage,
    isFirstPage,
    isLastPage,
    pageNumbers,
    loading,
    goToPage,
    nextPage,
    previousPage,
    firstPage,
    lastPage: lastPageAction,
    getPaginationInfo,
  }
}
