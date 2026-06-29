<script setup lang="ts">
import { computed } from 'vue'
import { CloudHail, CloudLightning, Snowflake, Sun, ThermometerSun, TriangleAlert, Wind } from '@lucide/vue'
import type { Component } from 'vue'
import { useI18n } from 'vue-i18n'

import { formatDateTime } from '@/lib/format'
import { cn } from '@/lib/utils'
import type { Alert } from '@/types/alert'

const props = defineProps<{ alert: Alert }>()
const { t, locale } = useI18n()

type Severity = 'high' | 'medium' | 'low'

const SEVERITY: Record<string, Severity> = {
  storm: 'high',
  extreme_heat: 'high',
  extreme_cold: 'high',
  air_quality: 'high',
  strong_wind: 'medium',
  rain: 'medium',
  snow: 'medium',
  uv_index: 'medium',
  fog: 'low',
}

const ICONS: Record<string, Component> = {
  storm: CloudLightning,
  extreme_heat: ThermometerSun,
  extreme_cold: Snowflake,
  air_quality: Wind,
  strong_wind: Wind,
  rain: CloudHail,
  snow: Snowflake,
  uv_index: Sun,
  fog: TriangleAlert,
}

const slug = computed(() => props.alert.type?.slug ?? '')
const severity = computed<Severity>(() => SEVERITY[slug.value] ?? 'low')
const icon = computed<Component>(() => ICONS[slug.value] ?? TriangleAlert)

const tone = computed(() =>
  ({
    high: 'border-destructive/40 bg-destructive/5 text-destructive',
    medium: 'border-amber-500/40 bg-amber-500/5 text-amber-600 dark:text-amber-400',
    low: 'border-sky-500/40 bg-sky-500/5 text-sky-600 dark:text-sky-400',
  })[severity.value],
)
</script>

<template>
  <div :class="cn('flex gap-3 rounded-lg border p-3', tone)">
    <div class="mt-0.5 shrink-0">
      <component :is="icon" class="size-5" />
    </div>
    <div class="min-w-0 flex-1">
      <div class="flex items-center justify-between gap-2">
        <p class="text-sm font-medium text-foreground">
          {{ alert.type?.name ?? t('alerts.generic') }}
        </p>
        <span
          v-if="!alert.read_at"
          class="size-2 shrink-0 rounded-full bg-current"
          :aria-label="t('alerts.unread')"
        />
      </div>
      <p class="mt-0.5 text-sm text-foreground/80">{{ alert.message }}</p>
      <p class="mt-1 text-xs text-muted-foreground">{{ formatDateTime(alert.created_at, locale) }}</p>
    </div>
  </div>
</template>
