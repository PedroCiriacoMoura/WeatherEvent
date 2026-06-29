import type { BadgeVariants } from '@/components/ui/badge'
import type { HealthRating } from '@/types/weather'

export const HEALTH_RATINGS: HealthRating[] = ['excellent', 'good', 'fair', 'poor', 'hazardous']

/** Maps a health rating to a Badge variant. */
export function ratingBadgeVariant(rating: HealthRating): NonNullable<BadgeVariants['variant']> {
  switch (rating) {
    case 'excellent':
    case 'good':
      return 'success'
    case 'fair':
      return 'warning'
    case 'poor':
    case 'hazardous':
      return 'destructive'
  }
}

/** Tailwind background class for a rating, used by the health-score progress bar. */
export function ratingIndicatorClass(rating: HealthRating): string {
  switch (rating) {
    case 'excellent':
      return 'bg-emerald-500'
    case 'good':
      return 'bg-green-500'
    case 'fair':
      return 'bg-amber-500'
    case 'poor':
      return 'bg-orange-500'
    case 'hazardous':
      return 'bg-red-500'
  }
}

/** Hex-ish colors for the risk distribution chart series, by rating. */
export const RISK_COLORS: Record<HealthRating, string> = {
  excellent: '#10b981',
  good: '#22c55e',
  fair: '#f59e0b',
  poor: '#f97316',
  hazardous: '#ef4444',
}
