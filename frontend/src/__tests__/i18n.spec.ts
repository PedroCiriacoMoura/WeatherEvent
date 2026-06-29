import { describe, it, expect } from 'vitest'

import i18n, { SUPPORTED_LOCALES } from '../i18n'

describe('i18n messages', () => {
  it('compiles the email placeholder (literal @) for every locale', () => {
    for (const locale of SUPPORTED_LOCALES) {
      i18n.global.locale.value = locale
      const value = i18n.global.t('auth.placeholders.email')
      expect(value).toContain('@')
    }
  })

  it('interpolates named params in toast/validation messages', () => {
    i18n.global.locale.value = 'en'
    expect(i18n.global.t('auth.toast.welcomeBack', { name: 'Jane' })).toContain('Jane')
    expect(i18n.global.t('auth.validation.minLength', { min: 8 })).toContain('8')
  })
})
