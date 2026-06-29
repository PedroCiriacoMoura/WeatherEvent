import { ref, type Ref } from 'vue'
import { getEvent } from '@/services/events'
import {
  getEventAlerts,
  getHealthScore,
  getRecommendations,
  getWeather,
} from '@/services/weather'
import { parseApiError } from '@/lib/api-error'
import type { Alert } from '@/types/alert'
import type { Event } from '@/types/event'
import type { HealthScore, Recommendation, Weather } from '@/types/weather'

interface Section<T> {
  data: Ref<T | null>
  loading: Ref<boolean>
  error: Ref<string | null>
}

function section<T>(): Section<T> {
  return {
    data: ref(null) as Ref<T | null>,
    loading: ref(false),
    error: ref<string | null>(null),
  }
}

/**
 * Aggregates everything the event detail page needs. Each section (event,
 * weather, health score, recommendations, alerts) loads and fails independently
 * so a weather-provider 502 doesn't blank out the rest of the page.
 */
export function useEvent(id: number | string) {
  const event = section<Event>()
  const weather = section<Weather>()
  const health = section<HealthScore>()
  const recommendations = section<Recommendation>()
  const alerts = ref<Alert[]>([])
  const alertsLoading = ref(false)
  const alertsError = ref<string | null>(null)

  async function run<T>(part: Section<T>, fn: () => Promise<T>): Promise<void> {
    part.loading.value = true
    part.error.value = null
    try {
      part.data.value = await fn()
    } catch (err) {
      part.error.value = parseApiError(err).message
    } finally {
      part.loading.value = false
    }
  }

  async function fetchEvent(): Promise<void> {
    await run(event, () => getEvent(id))
  }

  function fetchWeatherBundle(): void {
    void run(weather, () => getWeather(id))
    void run(health, () => getHealthScore(id))
    void run(recommendations, () => getRecommendations(id))
  }

  async function fetchAlerts(): Promise<void> {
    alertsLoading.value = true
    alertsError.value = null
    try {
      alerts.value = await getEventAlerts(id)
    } catch (err) {
      alertsError.value = parseApiError(err).message
    } finally {
      alertsLoading.value = false
    }
  }

  function fetchAll(): void {
    void fetchEvent()
    fetchWeatherBundle()
    void fetchAlerts()
  }

  return {
    event,
    weather,
    health,
    recommendations,
    alerts,
    alertsLoading,
    alertsError,
    fetchEvent,
    fetchWeatherBundle,
    fetchAlerts,
    fetchAll,
  }
}
