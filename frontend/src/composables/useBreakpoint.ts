import { onUnmounted, ref } from 'vue'

/**
 * Reactive media-query match. Defaults to Tailwind's `lg` breakpoint, used to
 * decide whether the sidebar renders inline (desktop) or as a drawer (mobile).
 */
export function useMediaQuery(query = '(min-width: 1024px)') {
  const matches = ref(false)

  if (typeof window !== 'undefined' && 'matchMedia' in window) {
    const media = window.matchMedia(query)
    matches.value = media.matches

    const onChange = (event: MediaQueryListEvent) => {
      matches.value = event.matches
    }
    media.addEventListener('change', onChange)
    onUnmounted(() => media.removeEventListener('change', onChange))
  }

  return matches
}

/** True on screens >= the `lg` breakpoint. */
export function useIsDesktop() {
  return useMediaQuery('(min-width: 1024px)')
}
