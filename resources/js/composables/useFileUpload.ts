import { ref, watch } from 'vue'
import { useFileDialog, useDropZone } from '@vueuse/core'

export interface UseFileUploadOptions {
  accept?: string
  multiple?: boolean
  reset?: boolean
  dropzone?: boolean
  onUpdate?: (files: File[], reset?: boolean) => void
}

export interface UseFileUploadReturn {
  isDragging: ReturnType<typeof ref<boolean>>
  open: () => void
  inputRef: ReturnType<typeof ref<HTMLInputElement | undefined>>
  dropzoneRef: ReturnType<typeof ref<HTMLElement | undefined>>
}

/**
 * Composable for handling file uploads with drag and drop support
 */
export function useFileUpload(options: UseFileUploadOptions = {}): UseFileUploadReturn {
  const {
    accept = '*',
    multiple = false,
    reset: resetOnOpen = false,
    dropzone: enableDropzone = true,
    onUpdate
  } = options

  const inputRef = ref<HTMLInputElement>()
  const dropzoneRef = ref<HTMLElement>()

  // Use VueUse's file dialog
  const { open: openDialog, onChange, reset: resetDialog } = useFileDialog({
    accept,
    multiple
  })

  // Handle file selection from input
  onChange((fileList) => {
    if (fileList && fileList.length > 0) {
      const files = Array.from(fileList)
      onUpdate?.(files, resetOnOpen)
    }
  })

  // Setup drag and drop
  const { isOverDropZone: isDragging } = useDropZone(dropzoneRef, {
    onDrop: (files, event) => {
      event.preventDefault()
      event.stopPropagation()

      if (!enableDropzone || !files || files.length === 0) {
        return
      }

      // Filter files based on accept attribute
      let validFiles = Array.from(files)

      if (accept && accept !== '*') {
        const acceptedTypes = accept.split(',').map(type => type.trim())
        validFiles = validFiles.filter(file => {
          return acceptedTypes.some(type => {
            // Check MIME type
            if (type.includes('/')) {
              if (type.endsWith('/*')) {
                const baseType = type.replace('/*', '')
                return file.type.startsWith(baseType)
              }
              return file.type === type
            }
            // Check file extension
            if (type.startsWith('.')) {
              return file.name.toLowerCase().endsWith(type.toLowerCase())
            }
            return false
          })
        })
      }

      // Handle multiple files
      if (!multiple && validFiles.length > 0) {
        validFiles = [validFiles[0]]
      }

      if (validFiles.length > 0) {
        onUpdate?.(validFiles, false)
      }
    },
    onOver: (event) => {
      if (!enableDropzone) return
      event.preventDefault()
      event.stopPropagation()
    },
    onLeave: (event) => {
      event.preventDefault()
      event.stopPropagation()
    }
  })

  const open = () => {
    if (resetOnOpen) {
      resetDialog()
    }
    openDialog()
  }

  // Watch for the input ref and assign it when available
  watch(() => inputRef.value, (input) => {
    if (input) {
      // Sync the VueUse file dialog input with our ref
      const vueUseInput = document.querySelector('input[type="file"]') as HTMLInputElement
      if (vueUseInput && vueUseInput !== input) {
        inputRef.value = vueUseInput
      }
    }
  })

  return {
    isDragging,
    open,
    inputRef,
    dropzoneRef
  }
}
