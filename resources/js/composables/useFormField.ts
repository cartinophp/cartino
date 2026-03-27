import { computed, inject, type ComputedRef } from 'vue'
import { useId } from './useId'

export interface FormFieldProps {
  id?: string
  name?: string
  disabled?: boolean
  required?: boolean
  'aria-label'?: string
  'aria-labelledby'?: string
  'aria-describedby'?: string
}

export interface UseFormFieldReturn {
  id: ComputedRef<string>
  name: ComputedRef<string | undefined>
  disabled: ComputedRef<boolean>
  ariaAttrs: ComputedRef<Record<string, string | undefined>>
  emitFormInput: () => void
  emitFormChange: () => void
}

/**
 * Composable for handling form field accessibility and form integration
 */
export function useFormField<T extends FormFieldProps>(props: T): UseFormFieldReturn {
  // Try to inject form context if available (for future form integration)
  const formContext = inject<any>('form-field', null)

  // Generate a unique ID if not provided
  const generatedId = useId()

  const id = computed(() => props.id || formContext?.id || generatedId.value)
  const name = computed(() => props.name || formContext?.name)
  const disabled = computed(() => props.disabled || formContext?.disabled || false)

  const ariaAttrs = computed(() => {
    const attrs: Record<string, string | undefined> = {}

    if (props['aria-label']) {
      attrs['aria-label'] = props['aria-label']
    }
    if (props['aria-labelledby']) {
      attrs['aria-labelledby'] = props['aria-labelledby']
    }
    if (props['aria-describedby']) {
      attrs['aria-describedby'] = props['aria-describedby']
    }
    if (props.required) {
      attrs['aria-required'] = 'true'
    }
    if (disabled.value) {
      attrs['aria-disabled'] = 'true'
    }

    return attrs
  })

  const emitFormInput = () => {
    // Trigger form input event for validation libraries
    if (formContext?.emitInput) {
      formContext.emitInput()
    }
  }

  const emitFormChange = () => {
    // Trigger form change event for validation libraries
    if (formContext?.emitChange) {
      formContext.emitChange()
    }
  }

  return {
    id,
    name,
    disabled,
    ariaAttrs,
    emitFormInput,
    emitFormChange
  }
}
