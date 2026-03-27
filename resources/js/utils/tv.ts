import { type ClassValue, clsx } from 'clsx'
import { twMerge } from 'tailwind-merge'

type TVConfig = {
  extend?: TVReturnType
  base?: ClassValue
  slots?: Record<string, ClassValue>
  variants?: Record<string, Record<string, ClassValue | Record<string, ClassValue>>>
  compoundVariants?: Array<Record<string, any>>
  defaultVariants?: Record<string, any>
}

type TVReturnType = (props?: Record<string, any>) => any

/**
 * Tailwind Variants (tv) - A utility for creating variant-based component styles
 * Simplified implementation compatible with the component requirements
 */
export function tv(config: TVConfig | TVReturnType): TVReturnType {
  // If config is already a function (from extend), return it as-is for chaining
  if (typeof config === 'function') {
    return config
  }

  const {
    extend,
    base = '',
    slots = {},
    variants = {},
    compoundVariants = [],
    defaultVariants = {}
  } = config

  return (props: Record<string, any> = {}) => {
    const mergedProps = { ...defaultVariants, ...props }
    const { class: className, ...variantProps } = mergedProps

    // If we have slots, return a function for each slot
    if (Object.keys(slots).length > 0) {
      const result: any = {}

      for (const [slotName, slotBase] of Object.entries(slots)) {
        result[slotName] = (slotProps: { class?: ClassValue } = {}) => {
          const classes: ClassValue[] = [slotBase]

          // Apply variant classes for this slot
          for (const [variantName, variantValues] of Object.entries(variants)) {
            const variantValue = variantProps[variantName]
            if (variantValue && variantValues[variantValue]) {
              const variantClass = variantValues[variantValue]
              // If variant class is an object, it has slot-specific classes
              if (typeof variantClass === 'object' && !Array.isArray(variantClass)) {
                if (variantClass[slotName]) {
                  classes.push(variantClass[slotName])
                }
              }
            }
          }

          // Apply compound variants
          for (const compound of compoundVariants) {
            const { class: compoundClass, ...compoundConditions } = compound
            const matches = Object.entries(compoundConditions).every(
              ([key, value]) => variantProps[key] === value
            )
            if (matches && compoundClass) {
              if (typeof compoundClass === 'object' && compoundClass[slotName]) {
                classes.push(compoundClass[slotName])
              }
            }
          }

          classes.push(slotProps.class)
          return twMerge(clsx(classes))
        }
      }

      return result
    }

    // Simple variant mode (no slots)
    const classes: ClassValue[] = [base]

    // Apply variants
    for (const [variantName, variantValues] of Object.entries(variants)) {
      const variantValue = variantProps[variantName]
      if (variantValue && variantValues[variantValue]) {
        classes.push(variantValues[variantValue] as ClassValue)
      }
    }

    // Apply compound variants
    for (const compound of compoundVariants) {
      const { class: compoundClass, ...compoundConditions } = compound
      const matches = Object.entries(compoundConditions).every(
        ([key, value]) => variantProps[key] === value
      )
      if (matches) {
        classes.push(compoundClass)
      }
    }

    classes.push(className)
    return twMerge(clsx(classes))
  }
}
