import api from '@/lib/axios'
import type { Alert, AlertListParams } from '@/types/alert'
import type { Paginated } from '@/types/pagination'

interface Envelope<T> {
  data: T
}

export async function listAlerts(
  params: AlertListParams = {},
  signal?: AbortSignal,
): Promise<Paginated<Alert>> {
  const { data } = await api.get<Paginated<Alert>>('/alerts', { params, signal })
  return data
}

export async function markAlertRead(id: number | string): Promise<Alert> {
  const { data } = await api.patch<Envelope<Alert>>(`/alerts/${id}/read`)
  return data.data
}

export async function markAllAlertsRead(): Promise<number> {
  const { data } = await api.post<{ updated: number }>('/alerts/read-all')
  return data.updated
}
