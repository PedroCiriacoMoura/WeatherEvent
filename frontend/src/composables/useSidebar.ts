import { ref } from 'vue'

const STORAGE_KEY = 'sidebar:collapsed'

// Module-level singletons so every consumer shares the same sidebar state.
const collapsed = ref(false)
const mobileOpen = ref(false)
let initialized = false

function init(): void {
  if (initialized || typeof window === 'undefined') return
  initialized = true
  collapsed.value = localStorage.getItem(STORAGE_KEY) === 'true'
}

export function useSidebar() {
  init()

  function toggleCollapsed(): void {
    collapsed.value = !collapsed.value
    localStorage.setItem(STORAGE_KEY, String(collapsed.value))
  }

  function openMobile(): void {
    mobileOpen.value = true
  }

  function closeMobile(): void {
    mobileOpen.value = false
  }

  return { collapsed, mobileOpen, toggleCollapsed, openMobile, closeMobile }
}
