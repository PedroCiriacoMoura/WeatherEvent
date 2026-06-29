/**
 * Pure, i18n-agnostic field validators. Each validator returns a
 * {@link ValidationError} descriptor (translation key + params) or `null`
 * when the value is valid. The translation happens in `useForm`, keeping
 * these functions free of any vue-i18n dependency and easy to unit test.
 *
 * Rules mirror the Laravel backend (see `backend/app/Http/Requests`).
 */

export interface ValidationError {
  key: string
  params?: Record<string, string | number>
}

export type Validator = (value: string, values: Record<string, string>) => ValidationError | null

const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

export function required(messageKey = 'auth.validation.required'): Validator {
  return (value) => (value != null && String(value).trim() !== '' ? null : { key: messageKey })
}

export function email(messageKey = 'auth.validation.email'): Validator {
  return (value) => (!value || EMAIL_PATTERN.test(value) ? null : { key: messageKey })
}

export function minLength(min: number, messageKey = 'auth.validation.minLength'): Validator {
  return (value) => (!value || value.length >= min ? null : { key: messageKey, params: { min } })
}

export function maxLength(max: number, messageKey = 'auth.validation.maxLength'): Validator {
  return (value) => (!value || value.length <= max ? null : { key: messageKey, params: { max } })
}

/** Passes when the value equals another field's value (e.g. password confirmation). */
export function matches(field: string, messageKey = 'auth.validation.passwordsDontMatch'): Validator {
  return (value, values) => (value === values[field] ? null : { key: messageKey })
}
