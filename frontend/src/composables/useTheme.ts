import { computed, ref } from 'vue'

export type Theme = 'light' | 'dark' | 'system'

const STORAGE_KEY = 'theme'

const theme = ref<Theme>('system')
const systemPrefersDark = ref(false)
let initialized = false

function isDark(value: Theme): boolean {
  return value === 'dark' || (value === 'system' && systemPrefersDark.value)
}

function applyTheme(): void {
  document.documentElement.classList.toggle('dark', isDark(theme.value))
}

export function initTheme(): void {
  if (initialized) return
  initialized = true

  const saved = localStorage.getItem(STORAGE_KEY)
  if (saved === 'light' || saved === 'dark' || saved === 'system') {
    theme.value = saved
  }

  const media = window.matchMedia('(prefers-color-scheme: dark)')
  systemPrefersDark.value = media.matches
  media.addEventListener('change', (event) => {
    systemPrefersDark.value = event.matches
    applyTheme()
  })

  applyTheme()
}

export function useTheme() {
  const resolvedTheme = computed<'light' | 'dark'>(() => (isDark(theme.value) ? 'dark' : 'light'))

  function setTheme(value: Theme): void {
    theme.value = value
    localStorage.setItem(STORAGE_KEY, value)
    applyTheme()
  }

  function toggleTheme(): void {
    setTheme(resolvedTheme.value === 'dark' ? 'light' : 'dark')
  }

  return { theme, resolvedTheme, setTheme, toggleTheme }
}
