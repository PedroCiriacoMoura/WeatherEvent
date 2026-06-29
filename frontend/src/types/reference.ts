/** Lightweight reference/enumeration row ({@link /api/event-categories} etc.). */
export interface ReferenceItem {
  id: number
  slug: string
  name: string
}

export type EventCategory = ReferenceItem
export type EventStatus = ReferenceItem
export type AlertType = ReferenceItem
