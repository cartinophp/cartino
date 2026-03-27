import { ref } from 'vue'

export function useConfirm() {
  const confirmState = ref({
    show: false,
    title: '',
    message: '',
    confirmText: 'Continue',
    cancelText: 'Cancel',
    variant: 'destructive',
    onConfirm: null,
    onCancel: null
  })

  const showConfirm = (options = {}) => {
    return new Promise((resolve) => {
      confirmState.value = {
        show: true,
        title: options.title || 'Are you absolutely sure?',
        message: options.message || 'This action cannot be undone.',
        confirmText: options.confirmText || 'Continue',
        cancelText: options.cancelText || 'Cancel',
        variant: options.variant || 'destructive',
        onConfirm: () => {
          confirmState.value.show = false
          if (options.onConfirm) options.onConfirm()
          resolve(true)
        },
        onCancel: () => {
          confirmState.value.show = false
          if (options.onCancel) options.onCancel()
          resolve(false)
        }
      }
    })
  }

  const hideConfirm = () => {
    confirmState.value.show = false
  }

  // Utility methods for common confirm dialogs
  const confirmDelete = (itemName = 'this item', onConfirm) => {
    return showConfirm({
      title: 'Delete Item',
      message: `Are you sure you want to delete ${itemName}? This action cannot be undone.`,
      confirmText: 'Delete',
      cancelText: 'Cancel',
      variant: 'destructive',
      onConfirm
    })
  }

  const confirmSave = (onConfirm) => {
    return showConfirm({
      title: 'Save Changes',
      message: 'Are you sure you want to save these changes?',
      confirmText: 'Save',
      cancelText: 'Cancel',
      variant: 'default',
      onConfirm
    })
  }

  const confirmDiscard = (onConfirm) => {
    return showConfirm({
      title: 'Discard Changes',
      message: 'You have unsaved changes. Are you sure you want to leave without saving?',
      confirmText: 'Discard',
      cancelText: 'Keep Editing',
      variant: 'destructive',
      onConfirm
    })
  }

  const confirmAction = (title, message, onConfirm, options = {}) => {
    return showConfirm({
      title,
      message,
      confirmText: options.confirmText || 'Continue',
      cancelText: options.cancelText || 'Cancel',
      variant: options.variant || 'default',
      onConfirm
    })
  }

  return {
    // State
    confirmState,

    // Main methods
    showConfirm,
    hideConfirm,

    // Utility methods
    confirmDelete,
    confirmSave,
    confirmDiscard,
    confirmAction
  }
}
