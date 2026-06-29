import { describe, expect, it } from 'vitest'

import { formatNumber, formatPercent, formatTemperature } from '@/lib/format'
import { ratingBadgeVariant, ratingIndicatorClass } from '@/lib/health'

describe('format helpers', () => {
  it('renders an em dash for nullish values', () => {
    expect(formatNumber(null)).toBe('—')
    expect(formatTemperature(undefined)).toBe('—')
    expect(formatPercent(null)).toBe('—')
  })

  it('appends the temperature unit', () => {
    expect(formatTemperature(21.4, 'en')).toBe('21.4°C')
  })

  it('prefixes positive percentages with a sign', () => {
    expect(formatPercent(12.5, 'en')).toBe('+12.5%')
    expect(formatPercent(-8, 'en')).toBe('-8%')
  })
})

describe('health rating mapping', () => {
  it('maps ratings to badge variants', () => {
    expect(ratingBadgeVariant('excellent')).toBe('success')
    expect(ratingBadgeVariant('fair')).toBe('warning')
    expect(ratingBadgeVariant('hazardous')).toBe('destructive')
  })

  it('maps ratings to indicator colors', () => {
    expect(ratingIndicatorClass('good')).toContain('bg-')
    expect(ratingIndicatorClass('poor')).toContain('bg-')
  })
})
