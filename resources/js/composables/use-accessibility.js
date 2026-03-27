import { onMounted, onUnmounted, ref } from 'vue'

/**
 * Composable for managing focus trap in modals/dialogs
 */
export function useFocusTrap() {
  const trapElement = ref(null)
  const previouslyFocusedElement = ref(null)

  const focusableSelectors = [
    'a[href]',
    'button:not([disabled])',
    'textarea:not([disabled])',
    'input:not([disabled])',
    'select:not([disabled])',
    '[tabindex]:not([tabindex="-1"])',
  ].join(',')

  const getFocusableElements = () => {
    if (!trapElement.value) return []
    return Array.from(trapElement.value.querySelectorAll(focusableSelectors))
  }

  const handleKeyDown = (e) => {
    if (e.key !== 'Tab') return

    const focusableElements = getFocusableElements()
    if (focusableElements.length === 0) return

    const firstElement = focusableElements[0]
    const lastElement = focusableElements[focusableElements.length - 1]

    if (e.shiftKey) {
      // Shift + Tab
      if (document.activeElement === firstElement) {
        e.preventDefault()
        lastElement.focus()
      }
    } else {
      // Tab
      if (document.activeElement === lastElement) {
        e.preventDefault()
        firstElement.focus()
      }
    }
  }

  const activate = (element) => {
    trapElement.value = element
    previouslyFocusedElement.value = document.activeElement

    // Focus first focusable element
    const focusableElements = getFocusableElements()
    if (focusableElements.length > 0) {
      focusableElements[0].focus()
    }

    document.addEventListener('keydown', handleKeyDown)
  }

  const deactivate = () => {
    document.removeEventListener('keydown', handleKeyDown)

    // Restore focus
    if (previouslyFocusedElement.value) {
      previouslyFocusedElement.value.focus()
    }

    trapElement.value = null
    previouslyFocusedElement.value = null
  }

  onUnmounted(() => {
    deactivate()
  })

  return {
    activate,
    deactivate,
  }
}

/**
 * Composable for managing announcements to screen readers
 */
export function useScreenReaderAnnounce() {
  const announceElement = ref(null)

  onMounted(() => {
    // Create live region if doesn't exist
    let liveRegion = document.getElementById('sr-live-region')

    if (!liveRegion) {
      liveRegion = document.createElement('div')
      liveRegion.id = 'sr-live-region'
      liveRegion.setAttribute('role', 'status')
      liveRegion.setAttribute('aria-live', 'polite')
      liveRegion.setAttribute('aria-atomic', 'true')
      liveRegion.className = 'sr-only'
      document.body.appendChild(liveRegion)
    }

    announceElement.value = liveRegion
  })

  const announce = (message, priority = 'polite') => {
    if (!announceElement.value) return

    announceElement.value.setAttribute('aria-live', priority)
    announceElement.value.textContent = ''

    // Small delay to ensure screen reader picks up the change
    setTimeout(() => {
      announceElement.value.textContent = message
    }, 100)
  }

  return {
    announce,
  }
}

/**
 * Composable for keyboard navigation in lists
 */
export function useKeyboardNavigation(items, options = {}) {
  const {
    loop = true,
    orientation = 'vertical', // 'vertical' | 'horizontal'
  } = options

  const currentIndex = ref(0)

  const getNextIndex = (current, direction) => {
    let next = current + direction

    if (loop) {
      if (next < 0) next = items.value.length - 1
      if (next >= items.value.length) next = 0
    } else {
      next = Math.max(0, Math.min(next, items.value.length - 1))
    }

    return next
  }

  const handleKeyDown = (e) => {
    const nextKey = orientation === 'vertical' ? 'ArrowDown' : 'ArrowRight'
    const prevKey = orientation === 'vertical' ? 'ArrowUp' : 'ArrowLeft'

    if (e.key === nextKey) {
      e.preventDefault()
      currentIndex.value = getNextIndex(currentIndex.value, 1)
      return true
    }

    if (e.key === prevKey) {
      e.preventDefault()
      currentIndex.value = getNextIndex(currentIndex.value, -1)
      return true
    }

    if (e.key === 'Home') {
      e.preventDefault()
      currentIndex.value = 0
      return true
    }

    if (e.key === 'End') {
      e.preventDefault()
      currentIndex.value = items.value.length - 1
      return true
    }

    return false
  }

  return {
    currentIndex,
    handleKeyDown,
  }
}

/**
 * Composable for managing skip links
 */
export function useSkipLinks() {
  const skipToContent = () => {
    const mainContent = document.getElementById('main-content')
    if (mainContent) {
      mainContent.setAttribute('tabindex', '-1')
      mainContent.focus()
      mainContent.removeAttribute('tabindex')
    }
  }

  return {
    skipToContent,
  }
}

/**
 * Generate unique IDs for form labels
 */
let idCounter = 0

export function useId(prefix = 'id') {
  return `${prefix}-${++idCounter}`
}
