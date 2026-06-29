import { computed, ref } from 'vue'
import { defineStore } from 'pinia'

import { getAlertTypes, getEventCategories, getEventStatuses } from '@/services/reference'
import type { SelectOption } from '@/components/ui/select'
import type { AlertType, EventCategory, EventStatus } from '@/types/reference'

/**
 * Globally-cached reference data (categories, statuses, alert types). Fetched
 * at most once per session and shared by the event form and list filters.
 */
export const useReferenceStore = defineStore('reference', () => {
  const categories = ref<EventCategory[]>([])
  const statuses = ref<EventStatus[]>([])
  const alertTypes = ref<AlertType[]>([])
  const loaded = ref(false)
  const loading = ref(false)

  const categoryOptions = computed<SelectOption[]>(() =>
    categories.value.map((c) => ({ label: c.name, value: c.id })),
  )
  const statusOptions = computed<SelectOption[]>(() =>
    statuses.value.map((s) => ({ label: s.name, value: s.id })),
  )

  async function ensureLoaded(force = false): Promise<void> {
    if (loaded.value && !force) return
    if (loading.value) return

    loading.value = true
    try {
      const [cats, stats, types] = await Promise.all([
        getEventCategories(),
        getEventStatuses(),
        getAlertTypes(),
      ])
      categories.value = cats
      statuses.value = stats
      alertTypes.value = types
      loaded.value = true
    } finally {
      loading.value = false
    }
  }

  return {
    categories,
    statuses,
    alertTypes,
    loaded,
    loading,
    categoryOptions,
    statusOptions,
    ensureLoaded,
  }
})
