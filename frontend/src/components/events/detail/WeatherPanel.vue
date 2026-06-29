<script setup lang="ts">
import { computed } from 'vue'
import { CloudOff, Droplets, Gauge, Thermometer, Wind } from '@lucide/vue'
import { useI18n } from 'vue-i18n'

import EmptyState from '@/components/common/EmptyState.vue'
import ErrorState from '@/components/common/ErrorState.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Skeleton } from '@/components/ui/skeleton'
import { formatNumber, formatTemperature } from '@/lib/format'
import type { Weather } from '@/types/weather'

const props = defineProps<{
  weather: Weather | null
  loading?: boolean
  error?: string | null
}>()

const emit = defineEmits<{ retry: [] }>()
const { t, locale } = useI18n()

const metrics = computed(() => {
  const current = props.weather?.current
  if (!current) return []
  return [
    {
      icon: Thermometer,
      label: t('events.weather.feelsLike'),
      value: formatTemperature(current.feels_like, locale.value),
    },
    {
      icon: Droplets,
      label: t('events.weather.humidity'),
      value: current.humidity != null ? `${formatNumber(current.humidity, locale.value)}%` : '—',
    },
    {
      icon: Wind,
      label: t('events.weather.wind'),
      value: current.wind_speed != null ? `${formatNumber(current.wind_speed, locale.value)} m/s` : '—',
    },
    {
      icon: Gauge,
      label: t('events.weather.pressure'),
      value: current.pressure != null ? `${formatNumber(current.pressure, locale.value)} hPa` : '—',
    },
  ]
})
</script>

<template>
  <Card class="gap-4">
    <CardHeader class="pb-0">
      <CardTitle class="text-base">{{ t('events.detail.weather') }}</CardTitle>
    </CardHeader>
    <CardContent>
      <ErrorState v-if="error" :message="error" :title="t('events.weather.unavailable')" @retry="emit('retry')" />

      <div v-else-if="loading" class="space-y-4">
        <Skeleton class="h-12 w-32" />
        <div class="grid grid-cols-2 gap-3">
          <Skeleton v-for="n in 4" :key="n" class="h-16" />
        </div>
      </div>

      <EmptyState
        v-else-if="!weather?.current"
        :icon="CloudOff"
        :title="t('events.weather.unavailable')"
      />

      <div v-else class="space-y-5">
        <div class="flex items-center gap-4">
          <span class="text-5xl font-semibold tracking-tight tabular-nums">
            {{ formatTemperature(weather.current.temperature, locale) }}
          </span>
          <div>
            <p class="font-medium capitalize">{{ weather.current.condition ?? '—' }}</p>
            <p class="text-sm text-muted-foreground capitalize">
              {{ weather.current.description ?? '' }}
            </p>
          </div>
        </div>

        <dl class="grid grid-cols-2 gap-3">
          <div
            v-for="metric in metrics"
            :key="metric.label"
            class="flex items-center gap-3 rounded-lg border bg-muted/30 px-3 py-2.5"
          >
            <component :is="metric.icon" class="size-4 shrink-0 text-muted-foreground" />
            <div class="min-w-0">
              <dt class="text-xs text-muted-foreground">{{ metric.label }}</dt>
              <dd class="text-sm font-medium tabular-nums">{{ metric.value }}</dd>
            </div>
          </div>
        </dl>
      </div>
    </CardContent>
  </Card>
</template>
