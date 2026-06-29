import api from '@/lib/axios'
import type { Event, EventListParams, EventPayload } from '@/types/event'
import type { Paginated } from '@/types/pagination'

interface SingleEnvelope {
  data: Event
}

export async function listEvents(
  params: EventListParams = {},
  signal?: AbortSignal,
): Promise<Paginated<Event>> {
  const { data } = await api.get<Paginated<Event>>('/events', { params, signal })
  return data
}

export async function getEvent(id: number | string, signal?: AbortSignal): Promise<Event> {
  const { data } = await api.get<SingleEnvelope>(`/events/${id}`, { signal })
  return data.data
}

export async function createEvent(payload: EventPayload): Promise<Event> {
  const { data } = await api.post<SingleEnvelope>('/events', payload)
  return data.data
}

export async function updateEvent(
  id: number | string,
  payload: Partial<EventPayload>,
): Promise<Event> {
  const { data } = await api.put<SingleEnvelope>(`/events/${id}`, payload)
  return data.data
}

export async function deleteEvent(id: number | string): Promise<void> {
  await api.delete(`/events/${id}`)
}
