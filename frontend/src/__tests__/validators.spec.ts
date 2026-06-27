import { describe, it, expect } from 'vitest'

import { email, matches, maxLength, minLength, required } from '../lib/validators'

describe('validators', () => {
  it('required flags empty / whitespace values', () => {
    expect(required()('', {})).toEqual({ key: 'auth.validation.required' })
    expect(required()('   ', {})).toEqual({ key: 'auth.validation.required' })
    expect(required()('value', {})).toBeNull()
  })

  it('email validates the address format', () => {
    expect(email()('not-an-email', {})).toEqual({ key: 'auth.validation.email' })
    expect(email()('jane@company.com', {})).toBeNull()
  })

  it('minLength / maxLength carry their params', () => {
    expect(minLength(8)('short', {})).toEqual({
      key: 'auth.validation.minLength',
      params: { min: 8 },
    })
    expect(minLength(8)('long-enough', {})).toBeNull()
    expect(maxLength(3)('toolong', {})).toEqual({
      key: 'auth.validation.maxLength',
      params: { max: 3 },
    })
  })

  it('matches compares against another field', () => {
    expect(matches('password')('a', { password: 'b' })).toEqual({
      key: 'auth.validation.passwordsDontMatch',
    })
    expect(matches('password')('secret', { password: 'secret' })).toBeNull()
  })
})
