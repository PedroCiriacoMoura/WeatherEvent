import { ref } from 'vue'
import { getDashboardCharts, getDashboardStats } from '@/services/dashboard'
import { parseApiError } from '@/lib/api-error'
import type { DashboardCharts, DashboardStats } from '@/types/dashboard'

/**
 * Loads dashboard stats and chart series independently so a slow chart query
 * never blocks the stat cards from rendering.
 */
export function useDashboard() {
  const stats = ref<DashboardStats | null>(null)
  const statsLoading = ref(false)
  const statsError = ref<string | null>(null)

  const charts = ref<DashboardCharts | null>(null)
  const chartsLoading = ref(false)
  const chartsError = ref<string | null>(null)

  async function fetchStats(): Promise<void> {
    statsLoading.value = true
    statsError.value = null
    try {
      stats.value = await getDashboardStats()
    } catch (err) {
      statsError.value = parseApiError(err).message
    } finally {
      statsLoading.value = false
    }
  }

  async function fetchCharts(): Promise<void> {
    chartsLoading.value = true
    chartsError.value = null
    try {
      charts.value = await getDashboardCharts()
    } catch (err) {
      chartsError.value = parseApiError(err).message
    } finally {
      chartsLoading.value = false
    }
  }

  function fetchAll(): void {
    void fetchStats()
    void fetchCharts()
  }

  return {
    stats,
    statsLoading,
    statsError,
    charts,
    chartsLoading,
    chartsError,
    fetchStats,
    fetchCharts,
    fetchAll,
  }
}
