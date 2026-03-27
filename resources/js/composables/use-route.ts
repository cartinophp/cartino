import { usePage } from '@inertiajs/vue3'

/**
 * Composable for working with Ziggy routes
 */
export function useRoute() {
  const page = usePage()

  /**
   * Generate a URL for the given route
   */
  const route = (
    name: string,
    params: Record<string, any> = {},
    absolute: boolean = false
  ): string => {
    if (typeof window.route === 'undefined') {
      console.warn('Ziggy route helper is not available')
      return '#'
    }

    return window.route(name, params, absolute)
  }

  /**
   * Check if the current route matches the given name
   */
  const routeIs = (name: string | string[]): boolean => {
    const currentRoute = page.props.ziggy?.location || window.location.pathname

    if (Array.isArray(name)) {
      return name.some((n) => currentRoute.includes(n))
    }

    return currentRoute.includes(name)
  }

  /**
   * Get the current route name
   */
  const currentRoute = (): string => {
    return page.props.ziggy?.route || ''
  }

  /**
   * Get current route parameters
   */
  const currentParams = (): Record<string, any> => {
    return page.props.ziggy?.params || {}
  }

  /**
   * Check if a parameter exists in the current route
   */
  const hasParam = (param: string): boolean => {
    const params = currentParams()
    return param in params
  }

  /**
   * Get a specific parameter from the current route
   */
  const getParam = (param: string, defaultValue: any = null): any => {
    const params = currentParams()
    return params[param] ?? defaultValue
  }

  return {
    route,
    routeIs,
    currentRoute,
    currentParams,
    hasParam,
    getParam,
  }
}

// Global type for window.route
declare global {
  interface Window {
    route: (
      name?: string,
      params?: Record<string, any>,
      absolute?: boolean
    ) => string
  }
}
