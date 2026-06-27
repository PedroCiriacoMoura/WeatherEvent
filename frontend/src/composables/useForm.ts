import { computed, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import type { Validator } from '@/lib/validators'

type TranslateFn = (key: string, named?: Record<string, unknown>) => string

export interface UseFormOptions<T extends Record<string, string>> {
  initialValues: T
  rules?: Partial<Record<keyof T, Validator[]>>
  onSubmit: (values: T) => Promise<void> | void
}

/**
 * Lightweight, strongly-typed form helper (no vee-validate / zod dependency).
 * Owns reactive values, per-field errors (translated lazily), touched state and
 * the submitting flag. Server-side (422) errors can be injected via
 * {@link setServerErrors} / {@link setFieldError}.
 */
export function useForm<T extends Record<string, string>>(options: UseFormOptions<T>) {
  const { t } = useI18n()
  const translate = t as unknown as TranslateFn

  const values = reactive({ ...options.initialValues }) as T
  const errors = ref<Partial<Record<keyof T, string>>>({})
  const touched = ref<Partial<Record<keyof T, boolean>>>({})
  const isSubmitting = ref(false)
  const formError = ref<string | null>(null)

  function runRules(field: keyof T): string | null {
    const rules = options.rules?.[field]
    if (!rules) return null
    for (const rule of rules) {
      const result = rule(values[field] ?? '', values as Record<string, string>)
      if (result) return translate(result.key, result.params)
    }
    return null
  }

  function validateField(field: keyof T): boolean {
    const message = runRules(field)
    if (message) {
      errors.value = { ...errors.value, [field]: message }
      return false
    }
    if (errors.value[field]) {
      const next = { ...errors.value }
      delete next[field]
      errors.value = next
    }
    return true
  }

  function validate(): boolean {
    let valid = true
    for (const field in options.rules) {
      if (!validateField(field as keyof T)) valid = false
    }
    return valid
  }

  function handleBlur(field: keyof T): void {
    touched.value = { ...touched.value, [field]: true }
    validateField(field)
  }

  function setFieldError(field: keyof T, message: string): void {
    touched.value = { ...touched.value, [field]: true }
    errors.value = { ...errors.value, [field]: message }
  }

  function setServerErrors(fieldErrors: Record<string, string>): void {
    for (const [field, message] of Object.entries(fieldErrors)) {
      if (field in values) setFieldError(field as keyof T, message)
    }
  }

  function reset(): void {
    Object.assign(values, options.initialValues)
    errors.value = {}
    touched.value = {}
    formError.value = null
  }

  async function submit(): Promise<void> {
    formError.value = null

    const allTouched: Partial<Record<keyof T, boolean>> = {}
    for (const field in values) allTouched[field as keyof T] = true
    touched.value = allTouched

    if (!validate()) return

    isSubmitting.value = true
    try {
      await options.onSubmit({ ...(values as T) })
    } finally {
      isSubmitting.value = false
    }
  }

  const isValid = computed(() => Object.keys(errors.value).length === 0)

  return {
    values,
    errors,
    touched,
    isSubmitting,
    formError,
    isValid,
    validateField,
    validate,
    handleBlur,
    setFieldError,
    setServerErrors,
    reset,
    submit,
  }
}
