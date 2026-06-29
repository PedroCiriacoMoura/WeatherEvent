import api from '@/lib/axios'
import type { Alert } from '@/types/alert'
import type { HealthScore, Recommendation, Weather } from '@/types/weather'

interface Envelope<T> {
  data: T
}

export async function getWeather(eventId: number | string, signal?: AbortSignal): Promise<Weather> {
  const { data } = await api.get<Envelope<Weather>>(`/events/${eventId}/weather`, { signal })
  return data.data
}

export async function getHealthScore(
  eventId: number | string,
  signal?: AbortSignal,
): Promise<HealthScore> {
  const { data } = await api.get<Envelope<HealthScore>>(`/events/${eventId}/health-score`, { signal })
  return data.data
}

export async function getRecommendations(
  eventId: number | string,
  signal?: AbortSignal,
): Promise<Recommendation> {
  const { data } = await api.get<Envelope<Recommendation>>(`/events/${eventId}/recommendations`, {
    signal,
  })
  return data.data
}

export async function getEventAlerts(
  eventId: number | string,
  signal?: AbortSignal,
): Promise<Alert[]> {
  const { data } = await api.get<Envelope<Alert[]>>(`/events/${eventId}/alerts`, { signal })
  return data.data
}
