import type { ReferenceItem } from './reference'
import type { SortDirection } from './pagination'

export interface Coordinates {
  latitude: number
  longitude: number
}

export interface EventRelation {
  id: number
  slug: string
  name: string
}

export interface EventOwner {
  id: number
  name: string
}

export interface Event {
  id: number
  name: string
  description: string | null
  city: string
  country: string
  coordinates: Coordinates | null
  starts_at: string
  ends_at: string | null
  timezone: string
  attendees: number
  is_outdoor: boolean
  category?: EventRelation
  status?: EventRelation
  user?: EventOwner
  created_at: string
  updated_at: string
}

/** Sort fields whitelisted by the backend `IndexEventRequest::SORTABLE`. */
export type EventSortField = 'name' | 'starts_at' | 'ends_at' | 'attendees' | 'created_at'

export interface EventListParams {
  category_id?: number
  status_id?: number
  city?: string
  country?: string
  is_outdoor?: boolean
  starts_after?: string
  starts_before?: string
  search?: string
  sort?: EventSortField
  direction?: SortDirection
  per_page?: number
  page?: number
}

/** Payload accepted by POST/PUT `/api/events`. */
export interface EventPayload {
  category_id: number
  status_id: number
  name: string
  description?: string | null
  city: string
  country: string
  latitude: number
  longitude: number
  starts_at: string
  ends_at?: string | null
  timezone?: string | null
  attendees?: number | null
  is_outdoor: boolean
}

// Re-exported for convenience when consuming reference data alongside events.
export type { ReferenceItem }
