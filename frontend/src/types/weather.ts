export interface WeatherLocation {
  city: string
  country: string
  latitude: number
  longitude: number
}

export interface CurrentWeather {
  temperature: number | null
  feels_like: number | null
  humidity: number | null
  pressure: number | null
  wind_speed: number | null
  wind_gust: number | null
  clouds: number | null
  rain: number
  snow: number
  condition: string | null
  description: string | null
}

export interface ForecastSlot {
  at: string
  temperature: number | null
  feels_like: number | null
  humidity: number | null
  wind_speed: number | null
  wind_gust: number | null
  rain: number
  snow: number
  precipitation_probability: number | null
  condition: string | null
  description: string | null
}

export interface AirQuality {
  aqi: number | null
  components: Record<string, number>
}

export interface Weather {
  location: WeatherLocation
  current: CurrentWeather
  forecast: ForecastSlot[]
  air_quality: AirQuality
  units: string
  retrieved_at: string
}

export type HealthRating = 'excellent' | 'good' | 'fair' | 'poor' | 'hazardous'

export interface HealthScore {
  score: number
  rating: HealthRating
  is_outdoor: boolean
  factors: {
    temperature: number
    wind: number
    precipitation: number
    air_quality: number
  }
}

export interface Recommendation {
  locale: string
  rating: HealthRating
  summary: string
  items: string[]
}
