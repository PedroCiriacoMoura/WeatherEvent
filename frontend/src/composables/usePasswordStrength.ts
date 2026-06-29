import { computed, type Ref } from 'vue'

export function evaluatePasswordStrength(password: string): number {
  if (!password) return 0

  let score = 0
  if (password.length >= 8) score += 1
  if (password.length >= 12) score += 1
  if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score += 1
  if (/\d/.test(password)) score += 1
  if (/[^A-Za-z0-9]/.test(password)) score += 1

  return Math.min(score, 4)
}

const STRENGTH_KEYS = [
  'auth.strength.veryWeak',
  'auth.strength.weak',
  'auth.strength.fair',
  'auth.strength.good',
  'auth.strength.strong',
] as const

export function usePasswordStrength(password: Ref<string>) {
  const score = computed(() => evaluatePasswordStrength(password.value))
  const labelKey = computed<string>(() => STRENGTH_KEYS[score.value] ?? STRENGTH_KEYS[0])
  return { score, labelKey }
}
