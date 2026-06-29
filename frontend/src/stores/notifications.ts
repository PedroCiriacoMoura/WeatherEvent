import { computed, ref } from 'vue'
import { defineStore } from 'pinia'

import { listAlerts, markAlertRead, markAllAlertsRead } from '@/services/alerts'
import type { Alert } from '@/types/alert'

/**
 * Global alert/notification state surfaced by the header bell. Holds the most
 * recent alerts plus the unread count.
 */
export const useNotificationsStore = defineStore('notifications', () => {
  const alerts = ref<Alert[]>([])
  const loading = ref(false)
  const loaded = ref(false)

  const unreadCount = computed(() => alerts.value.filter((a) => a.read_at === null).length)
  const hasUnread = computed(() => unreadCount.value > 0)

  async function fetch(): Promise<void> {
    loading.value = true
    try {
      const result = await listAlerts({ per_page: 10 })
      alerts.value = result.data
      loaded.value = true
    } finally {
      loading.value = false
    }
  }

  async function markRead(id: number): Promise<void> {
    const updated = await markAlertRead(id)
    const index = alerts.value.findIndex((a) => a.id === id)
    if (index !== -1) alerts.value[index] = updated
  }

  async function markAllRead(): Promise<void> {
    await markAllAlertsRead()
    const now = new Date().toISOString()
    alerts.value = alerts.value.map((a) => (a.read_at ? a : { ...a, read_at: now }))
  }

  function reset(): void {
    alerts.value = []
    loaded.value = false
  }

  return { alerts, loading, loaded, unreadCount, hasUnread, fetch, markRead, markAllRead, reset }
})
