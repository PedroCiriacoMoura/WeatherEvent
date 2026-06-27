import { describe, it, expect } from 'vitest'

import { evaluatePasswordStrength } from '../composables/usePasswordStrength'

describe('evaluatePasswordStrength', () => {
  it('returns 0 for an empty password', () => {
    expect(evaluatePasswordStrength('')).toBe(0)
  })

  it('scores short, simple passwords low', () => {
    expect(evaluatePasswordStrength('abc')).toBeLessThanOrEqual(1)
  })

  it('caps a diverse, long password at 4', () => {
    expect(evaluatePasswordStrength('Abcdef1!xyz9')).toBe(4)
  })
})
