<script setup lang="ts">
import { CalendarOff, CloudRain, Droplets, Wind } from '@lucide/vue'
import { useI18n } from 'vue-i18n'

import EmptyState from '@/components/common/EmptyState.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Skeleton } from '@/components/ui/skeleton'
import { formatDateTime, formatNumber, formatTemperature } from '@/lib/format'
import type { ForecastSlot } from '@/types/weather'

defineProps<{
  forecast: ForecastSlot[]
  loading?: boolean
}>()

const { t, locale } = useI18n()
</script>

<template>
  <Card class="gap-4">
    <CardHeader class="pb-0">
      <CardTitle class="text-base">{{ t('events.detail.forecast') }}</CardTitle>
    </CardHeader>
    <CardContent>
      <div v-if="loading" class="space-y-2">
        <Skeleton v-for="n in 3" :key="n" class="h-14 w-full" />
      </div>

      <EmptyState
        v-else-if="!forecast.length"
        :icon="CalendarOff"
        :title="t('events.forecast.empty')"
      />

      <ul v-else class="divide-y divide-border">
        <li
          v-for="(slot, index) in forecast"
          :key="index"
          class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0"
        >
          <div class="min-w-0">
            <p class="text-sm font-medium">{{ formatDateTime(slot.at, locale) }}</p>
            <p class="truncate text-xs text-muted-foreground capitalize">
              {{ slot.description ?? slot.condition ?? '' }}
            </p>
          </div>
          <div class="flex items-center gap-4 text-sm">
            <span class="inline-flex items-center gap-1 text-muted-foreground">
              <Droplets class="size-3.5" />
              {{ slot.humidity != null ? `${formatNumber(slot.humidity, locale)}%` : '—' }}
            </span>
            <span class="inline-flex items-center gap-1 text-muted-foreground">
              <Wind class="size-3.5" />
              {{ slot.wind_speed != null ? `${formatNumber(slot.wind_speed, locale)}` : '—' }}
            </span>
            <span
              v-if="slot.precipitation_probability != null"
              class="inline-flex items-center gap-1 text-muted-foreground"
            >
              <CloudRain class="size-3.5" />
              {{ Math.round(slot.precipitation_probability * 100) }}%
            </span>
            <span class="w-14 text-right font-medium tabular-nums">
              {{ formatTemperature(slot.temperature, locale) }}
            </span>
          </div>
        </li>
      </ul>
    </CardContent>
  </Card>
</template>
