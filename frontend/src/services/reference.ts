import api from '@/lib/axios'
import type { AlertType, EventCategory, EventStatus } from '@/types/reference'

interface Envelope<T> {
  data: T
}

export async function getEventCategories(signal?: AbortSignal): Promise<EventCategory[]> {
  const { data } = await api.get<Envelope<EventCategory[]>>('/event-categories', { signal })
  return data.data
}

export async function getEventStatuses(signal?: AbortSignal): Promise<EventStatus[]> {
  const { data } = await api.get<Envelope<EventStatus[]>>('/event-statuses', { signal })
  return data.data
}

export async function getAlertTypes(signal?: AbortSignal): Promise<AlertType[]> {
  const { data } = await api.get<Envelope<AlertType[]>>('/alert-types', { signal })
  return data.data
}
