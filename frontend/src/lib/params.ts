export type QueryValue = string | number | boolean | undefined | null

export function toQueryParams(params: object): Record<string, string | number> {
  const out: Record<string, string | number> = {}
  for (const [key, value] of Object.entries(params)) {
    if (value === undefined || value === null || value === '') continue
    out[key] = typeof value === 'boolean' ? (value ? 1 : 0) : (value as string | number)
  }
  return out
}
