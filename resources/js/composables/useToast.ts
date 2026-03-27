import { ref } from 'vue'

export interface Toast {
  id: string
  title: string
  description?: string
  variant?: 'default' | 'destructive' | 'success'
  duration?: number
}

const toasts = ref<Toast[]>([])

export function useToast() {
  const toast = (options: Omit<Toast, 'id'>) => {
    const id = Math.random().toString(36).substring(2, 9)
    const duration = options.duration ?? 5000

    const newToast: Toast = {
      id,
      ...options,
    }

    toasts.value.push(newToast)

    if (duration > 0) {
      setTimeout(() => {
        dismiss(id)
      }, duration)
    }

    return {
      id,
      dismiss: () => dismiss(id),
      update: (updatedOptions: Partial<Toast>) => {
        const index = toasts.value.findIndex(t => t.id === id)
        if (index > -1) {
          toasts.value[index] = { ...toasts.value[index], ...updatedOptions }
        }
      },
    }
  }

  const dismiss = (id: string) => {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }

  const dismissAll = () => {
    toasts.value = []
  }

  return {
    toast,
    dismiss,
    dismissAll,
    toasts: toasts.value,
  }
}
