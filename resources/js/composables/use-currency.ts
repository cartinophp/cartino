import { ref, computed } from 'vue'

export interface CurrencyFormatOptions {
  locale?: string
  currency?: string
  minimumFractionDigits?: number
  maximumFractionDigits?: number
}

export function useCurrency(defaultOptions: CurrencyFormatOptions = {}) {
  const locale = ref(defaultOptions.locale || 'it-IT')
  const currency = ref(defaultOptions.currency || 'EUR')

  const formatCurrency = (
    value: number | string | null | undefined,
    options: CurrencyFormatOptions = {}
  ): string => {
    const numValue = typeof value === 'string' ? parseFloat(value) : value

    if (numValue === null || numValue === undefined || isNaN(numValue)) {
      return formatCurrency(0, options)
    }

    const formatter = new Intl.NumberFormat(options.locale || locale.value, {
      style: 'currency',
      currency: options.currency || currency.value,
      minimumFractionDigits: options.minimumFractionDigits ?? 2,
      maximumFractionDigits: options.maximumFractionDigits ?? 2,
    })

    return formatter.format(numValue)
  }

  const formatNumber = (
    value: number | string | null | undefined,
    options: Intl.NumberFormatOptions = {}
  ): string => {
    const numValue = typeof value === 'string' ? parseFloat(value) : value

    if (numValue === null || numValue === undefined || isNaN(numValue)) {
      return '0'
    }

    const formatter = new Intl.NumberFormat(locale.value, options)
    return formatter.format(numValue)
  }

  const parseCurrency = (value: string): number => {
    const cleaned = value.replace(/[^\d,.-]/g, '').replace(',', '.')
    return parseFloat(cleaned) || 0
  }

  const getCurrencySymbol = (currencyCode?: string): string => {
    const formatter = new Intl.NumberFormat(locale.value, {
      style: 'currency',
      currency: currencyCode || currency.value,
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    })

    return formatter.format(0).replace(/\d/g, '').trim()
  }

  return {
    locale,
    currency,
    formatCurrency,
    formatNumber,
    parseCurrency,
    getCurrencySymbol,
  }
}
