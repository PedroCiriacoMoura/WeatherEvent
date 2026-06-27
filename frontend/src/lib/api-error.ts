import { AxiosError } from 'axios'

export interface ParsedApiError {
  /** Best-effort human-readable message coming from the server (may be in English). */
  message: string
  /** Field-level validation messages keyed by field name (Laravel 422 `errors`). */
  fieldErrors: Record<string, string>
  /** HTTP status code, when a response was received. */
  status?: number
  /** True when the request never reached the server (offline, CORS, timeout). */
  isNetwork: boolean
}

interface LaravelErrorPayload {
  message?: string
  errors?: Record<string, string[]>
}

/**
 * Normalizes an unknown thrown value into a {@link ParsedApiError}. Understands
 * Laravel's `{ message, errors: { field: [...] } }` 422 shape so callers can map
 * field errors inline and decide whether to show a toast.
 */
export function parseApiError(error: unknown): ParsedApiError {
  if (error instanceof AxiosError) {
    const status = error.response?.status
    const payload = error.response?.data as LaravelErrorPayload | undefined
    const isNetwork = error.response === undefined

    const fieldErrors: Record<string, string> = {}
    if (payload?.errors) {
      for (const [field, messages] of Object.entries(payload.errors)) {
        const first = Array.isArray(messages) ? messages[0] : undefined
        if (first) fieldErrors[field] = first
      }
    }

    return {
      message: payload?.message ?? error.message,
      fieldErrors,
      status,
      isNetwork,
    }
  }

  if (error instanceof Error) {
    return { message: error.message, fieldErrors: {}, isNetwork: false }
  }

  return { message: 'Unexpected error', fieldErrors: {}, isNetwork: false }
}
