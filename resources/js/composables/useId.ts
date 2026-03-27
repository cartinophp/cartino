import { computed, getCurrentInstance } from 'vue'

let counter = 0

/**
 * Generates a unique ID for component instances
 */
export function useId(prefix = 'cartino') {
  const instance = getCurrentInstance()
  const uid = instance?.uid ?? ++counter

  return computed(() => `${prefix}-${uid}`)
}
