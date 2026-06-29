import { createI18n } from 'vue-i18n'

import en from './locales/en.json'
import pt from './locales/pt.json'
import es from './locales/es.json'

export const SUPPORTED_LOCALES = ['en', 'pt', 'es'] as const
export type SupportedLocale = (typeof SUPPORTED_LOCALES)[number]

export const FALLBACK_LOCALE: SupportedLocale = 'en'

const STORAGE_KEY = 'locale'

function isSupported(locale: string): locale is SupportedLocale {
  return SUPPORTED_LOCALES.includes(locale as SupportedLocale)
}

function detectLocale(): SupportedLocale {
  const saved = localStorage.getItem(STORAGE_KEY)
  if (saved && isSupported(saved)) {
    return saved
  }

  const browser = navigator.language.split('-')[0] ?? ''
  if (isSupported(browser)) {
    return browser
  }

  return FALLBACK_LOCALE
}

const i18n = createI18n({
  legacy: false,
  locale: detectLocale(),
  fallbackLocale: FALLBACK_LOCALE,
  messages: { en, pt, es },
})

export function setLocale(locale: SupportedLocale): void {
  i18n.global.locale.value = locale
  localStorage.setItem(STORAGE_KEY, locale)
  document.documentElement.setAttribute('lang', locale)
}

document.documentElement.setAttribute('lang', i18n.global.locale.value)

export default i18n
