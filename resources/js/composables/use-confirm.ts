import { ref } from 'vue'

export interface ConfirmOptions {
  title?: string
  message?: string
  confirmText?: string
  cancelText?: string
  type?: 'warning' | 'danger' | 'info'
}

const isOpen = ref(false)
const currentOptions = ref<ConfirmOptions>({})
let resolvePromise: ((value: boolean) => void) | null = null

export function useConfirm() {
  const confirm = (options: ConfirmOptions = {}): Promise<boolean> => {
    currentOptions.value = {
      title: 'Confirm Action',
      message: 'Are you sure you want to proceed?',
      confirmText: 'Confirm',
      cancelText: 'Cancel',
      type: 'warning',
      ...options
    }
    
    isOpen.value = true
    
    return new Promise<boolean>((resolve) => {
      resolvePromise = resolve
    })
  }
  
  const handleConfirm = () => {
    isOpen.value = false
    if (resolvePromise) {
      resolvePromise(true)
      resolvePromise = null
    }
  }
  
  const handleCancel = () => {
    isOpen.value = false
    if (resolvePromise) {
      resolvePromise(false)
      resolvePromise = null
    }
  }
  
  return {
    isOpen,
    currentOptions,
    confirm,
    handleConfirm,
    handleCancel
  }
}
