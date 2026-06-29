import type { HealthRating } from './weather'

export interface DashboardStats {
  total_events: number
  upcoming_events: number
  critical_events: number
  average_temperature: number | null
  total_events_delta: number | null
  upcoming_events_delta: number | null
}

export interface ChartPoint {
  at: string
  value: number
}

export type RiskPoint = { at: string } & Record<HealthRating, number>

export interface DashboardCharts {
  temperature: ChartPoint[]
  rain: ChartPoint[]
  humidity: ChartPoint[]
  risk: RiskPoint[]
}
