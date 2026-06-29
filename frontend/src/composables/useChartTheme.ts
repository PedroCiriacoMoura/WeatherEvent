import { computed, ref, watch } from 'vue'
import type { ApexOptions } from 'apexcharts'
import { useTheme } from '@/composables/useTheme'

/**
 * Resolves a CSS custom property from the document root. Falls back to the
 * provided default when running before mount or when the var is unset.
 */
function cssVar(name: string, fallback: string): string {
  if (typeof window === 'undefined') return fallback
  const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim()
  return value || fallback
}

/**
 * Builds theme-aware ApexCharts options from the design-token CSS variables, so
 * charts recolor instantly when the user toggles light/dark.
 */
export function useChartTheme() {
  const { resolvedTheme } = useTheme()

  // Bump this token whenever the theme changes to force a fresh CSS-var read.
  const revision = ref(0)
  watch(resolvedTheme, () => {
    revision.value++
  })

  const palette = computed<string[]>(() => {
    void revision.value
    return [
      cssVar('--chart-1', '#6366f1'),
      cssVar('--chart-2', '#06b6d4'),
      cssVar('--chart-3', '#3b82f6'),
      cssVar('--chart-4', '#f59e0b'),
      cssVar('--chart-5', '#f97316'),
    ]
  })

  const foreColor = computed(() => {
    void revision.value
    return cssVar('--muted-foreground', resolvedTheme.value === 'dark' ? '#a1a1aa' : '#71717a')
  })

  const borderColor = computed(() => {
    void revision.value
    return resolvedTheme.value === 'dark' ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)'
  })

  function baseOptions(overrides: ApexOptions = {}): ApexOptions {
    const base: ApexOptions = {
      chart: {
        fontFamily: 'inherit',
        toolbar: { show: false },
        zoom: { enabled: false },
        background: 'transparent',
        animations: { speed: 400 },
        parentHeightOffset: 0,
      },
      theme: { mode: resolvedTheme.value },
      colors: palette.value,
      grid: {
        borderColor: borderColor.value,
        strokeDashArray: 4,
        padding: { left: 8, right: 8 },
      },
      dataLabels: { enabled: false },
      legend: {
        position: 'top',
        horizontalAlign: 'right',
        fontSize: '12px',
        labels: { colors: foreColor.value },
        markers: { strokeWidth: 0 },
      },
      tooltip: { theme: resolvedTheme.value },
      stroke: { width: 2, curve: 'smooth', lineCap: 'round' },
      xaxis: {
        axisBorder: { color: borderColor.value },
        axisTicks: { color: borderColor.value },
        labels: { style: { colors: foreColor.value, fontSize: '12px' } },
      },
      yaxis: {
        labels: { style: { colors: foreColor.value, fontSize: '12px' } },
      },
    }

    return mergeOptions(base, overrides)
  }

  return { baseOptions, palette, foreColor, borderColor }
}

/** Shallow-ish merge sufficient for ApexCharts option objects. */
function mergeOptions(base: ApexOptions, overrides: ApexOptions): ApexOptions {
  const result: Record<string, unknown> = { ...base }
  for (const [key, value] of Object.entries(overrides)) {
    const existing = result[key]
    if (
      value &&
      typeof value === 'object' &&
      !Array.isArray(value) &&
      existing &&
      typeof existing === 'object' &&
      !Array.isArray(existing)
    ) {
      result[key] = { ...(existing as object), ...(value as object) }
    } else {
      result[key] = value
    }
  }
  return result as ApexOptions
}
