/**
 * Locale-aware formatting helpers built on the `Intl` API. The active locale is
 * passed in by callers (typically `useI18n().locale.value`) so output follows
 * the user's chosen language without coupling this module to vue-i18n.
 */

export function formatDate(value: string | Date | null | undefined, locale = 'en'): string {
  if (!value) return '—'
  const date = value instanceof Date ? value : new Date(value)
  if (Number.isNaN(date.getTime())) return '—'
  return new Intl.DateTimeFormat(locale, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  }).format(date)
}

export function formatDateTime(value: string | Date | null | undefined, locale = 'en'): string {
  if (!value) return '—'
  const date = value instanceof Date ? value : new Date(value)
  if (Number.isNaN(date.getTime())) return '—'
  return new Intl.DateTimeFormat(locale, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  }).format(date)
}

export function formatTime(value: string | Date | null | undefined, locale = 'en'): string {
  if (!value) return '—'
  const date = value instanceof Date ? value : new Date(value)
  if (Number.isNaN(date.getTime())) return '—'
  return new Intl.DateTimeFormat(locale, { hour: 'numeric', minute: '2-digit' }).format(date)
}

export function formatNumber(value: number | null | undefined, locale = 'en'): string {
  if (value === null || value === undefined || Number.isNaN(value)) return '—'
  return new Intl.NumberFormat(locale).format(value)
}

export function formatTemperature(
  value: number | null | undefined,
  locale = 'en',
  unit: '°C' | '°F' = '°C',
): string {
  if (value === null || value === undefined || Number.isNaN(value)) return '—'
  return `${new Intl.NumberFormat(locale, { maximumFractionDigits: 1 }).format(value)}${unit}`
}

export function formatPercent(value: number | null | undefined, locale = 'en'): string {
  if (value === null || value === undefined || Number.isNaN(value)) return '—'
  const sign = value > 0 ? '+' : ''
  return `${sign}${new Intl.NumberFormat(locale, { maximumFractionDigits: 1 }).format(value)}%`
}
