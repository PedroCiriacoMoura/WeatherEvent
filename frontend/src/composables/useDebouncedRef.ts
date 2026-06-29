import { onScopeDispose, ref, watch, type Ref } from 'vue'

/**
 * Mirrors a source ref but only propagates changes after `delay` ms of quiet.
 * Useful for debouncing search inputs before triggering a request.
 */
export function useDebouncedRef<T>(source: Ref<T>, delay = 350): Ref<T> {
  const debounced = ref(source.value) as Ref<T>
  let timer: ReturnType<typeof setTimeout> | undefined

  watch(source, (value) => {
    if (timer) clearTimeout(timer)
    timer = setTimeout(() => {
      debounced.value = value
    }, delay)
  })

  onScopeDispose(() => {
    if (timer) clearTimeout(timer)
  })

  return debounced
}
