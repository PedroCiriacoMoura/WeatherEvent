import api from '@/lib/axios'
import type { DashboardCharts, DashboardStats } from '@/types/dashboard'

interface Envelope<T> {
  data: T
}

export async function getDashboardStats(signal?: AbortSignal): Promise<DashboardStats> {
  const { data } = await api.get<Envelope<DashboardStats>>('/dashboard/stats', { signal })
  return data.data
}

export async function getDashboardCharts(signal?: AbortSignal): Promise<DashboardCharts> {
  const { data } = await api.get<Envelope<DashboardCharts>>('/dashboard/charts', { signal })
  return data.data
}
