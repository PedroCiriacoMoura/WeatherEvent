import { computed, reactive, ref, watch } from 'vue'
import { listEvents } from '@/services/events'
import { parseApiError } from '@/lib/api-error'
import type { Event, EventListParams, EventSortField } from '@/types/event'
import type { PaginationMeta, SortDirection } from '@/types/pagination'

interface UseEventsListOptions {
  /** Params always merged into every request (e.g. dashboard upcoming filter). */
  baseParams?: Partial<EventListParams>
  /** Override default sort. */
  sort?: EventSortField
  direction?: SortDirection
  perPage?: number
  /** Skip the automatic initial fetch / watch (caller drives `fetch`). */
  immediate?: boolean
}

/**
 * Encapsulates the events index query: filters, search, sort, pagination,
 * loading/error state and in-flight request cancellation. Used by both the
 * Events list page and the dashboard's upcoming-events table.
 */
export function useEventsList(options: UseEventsListOptions = {}) {
  const items = ref<Event[]>([])
  const meta = ref<PaginationMeta | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const filters = reactive<EventListParams>({
    search: '',
    category_id: undefined,
    status_id: undefined,
    is_outdoor: undefined,
    sort: options.sort ?? 'starts_at',
    direction: options.direction ?? 'asc',
    per_page: options.perPage ?? 10,
    page: 1,
  })

  let controller: AbortController | null = null

  function buildParams(): EventListParams {
    const params: EventListParams = { ...options.baseParams }
    for (const [key, value] of Object.entries(filters)) {
      if (value !== undefined && value !== null && value !== '') {
        // @ts-expect-error index assignment across union keys
        params[key] = value
      }
    }
    return params
  }

  async function fetch(): Promise<void> {
    controller?.abort()
    controller = new AbortController()

    loading.value = true
    error.value = null

    try {
      const result = await listEvents(buildParams(), controller.signal)
      items.value = result.data
      meta.value = result.meta
    } catch (err) {
      if (controller.signal.aborted) return
      const parsed = parseApiError(err)
      if (parsed.status === undefined && parsed.message === 'canceled') return
      error.value = parsed.message
      items.value = []
      meta.value = null
    } finally {
      if (!controller.signal.aborted) loading.value = false
    }
  }

  function setPage(page: number): void {
    filters.page = page
  }

  function setSort(field: EventSortField): void {
    if (filters.sort === field) {
      filters.direction = filters.direction === 'asc' ? 'desc' : 'asc'
    } else {
      filters.sort = field
      filters.direction = 'asc'
    }
    filters.page = 1
  }

  function resetFilters(): void {
    filters.search = ''
    filters.category_id = undefined
    filters.status_id = undefined
    filters.is_outdoor = undefined
    filters.country = undefined
    filters.starts_after = undefined
    filters.starts_before = undefined
    filters.page = 1
  }

  const isEmpty = computed(() => !loading.value && !error.value && items.value.length === 0)

  // Any filter/sort change (except page) resets to the first page.
  watch(
    () => [
      filters.search,
      filters.category_id,
      filters.status_id,
      filters.is_outdoor,
      filters.country,
      filters.starts_after,
      filters.starts_before,
      filters.sort,
      filters.direction,
    ],
    () => {
      if (filters.page !== 1) {
        filters.page = 1
        return // page watcher will trigger the fetch
      }
      void fetch()
    },
  )

  watch(() => filters.page, () => void fetch())

  if (options.immediate !== false) {
    void fetch()
  }

  return {
    items,
    meta,
    loading,
    error,
    filters,
    isEmpty,
    fetch,
    setPage,
    setSort,
    resetFilters,
  }
}
