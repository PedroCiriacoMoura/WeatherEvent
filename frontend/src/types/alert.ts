export interface AlertTypeRef {
  id: number
  slug: string
  name: string
}

export interface AlertEventRef {
  id: number
  name: string
}

export interface Alert {
  id: number
  message: string
  locale: string | null
  read_at: string | null
  created_at: string
  type?: AlertTypeRef
  event?: AlertEventRef
}

export interface AlertListParams {
  unread?: boolean
  type_id?: number
  per_page?: number
  page?: number
}
