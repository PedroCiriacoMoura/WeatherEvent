<script setup lang="ts">
import { computed, type Component } from 'vue'
import { Info, TrendingDown, TrendingUp } from '@lucide/vue'
import { useI18n } from 'vue-i18n'

import { Card } from '@/components/ui/card'
import { Skeleton } from '@/components/ui/skeleton'
import { Tooltip } from '@/components/ui/tooltip'
import { formatPercent } from '@/lib/format'

const props = withDefaults(
  defineProps<{
    label: string
    value: string | number
    icon: Component
    /** Percentage change vs previous period. null hides the indicator. */
    delta?: number | null
    tooltip?: string
    loading?: boolean
    /** When true, renders a muted placeholder value (no data). */
    empty?: boolean
  }>(),
  { delta: null, loading: false, empty: false },
)

const { t, locale } = useI18n()

const deltaTone = computed(() => {
  if (props.delta === null || props.delta === undefined || props.delta === 0) return 'muted'
  return props.delta > 0 ? 'up' : 'down'
})
</script>

<template>
  <Card class="gap-0 p-5">
    <div class="flex items-center justify-between">
      <p class="text-sm font-medium text-muted-foreground">{{ label }}</p>
      <div class="flex items-center gap-1">
        <Tooltip v-if="tooltip">
          <button type="button" class="text-muted-foreground/70 transition-colors hover:text-foreground">
            <Info class="size-3.5" />
          </button>
          <template #content>{{ tooltip }}</template>
        </Tooltip>
        <div class="flex size-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
          <component :is="icon" class="size-4.5" />
        </div>
      </div>
    </div>

    <div class="mt-3">
      <Skeleton v-if="loading" class="h-9 w-24" />
      <p v-else class="text-3xl font-semibold tracking-tight tabular-nums" :class="empty && 'text-muted-foreground'">
        {{ empty ? '—' : value }}
      </p>
    </div>

    <div v-if="!loading && delta !== null" class="mt-2 flex items-center gap-1 text-xs">
      <span
        class="inline-flex items-center gap-0.5 font-medium"
        :class="{
          'text-emerald-600 dark:text-emerald-400': deltaTone === 'up',
          'text-red-600 dark:text-red-400': deltaTone === 'down',
          'text-muted-foreground': deltaTone === 'muted',
        }"
      >
        <TrendingUp v-if="deltaTone === 'up'" class="size-3.5" />
        <TrendingDown v-else-if="deltaTone === 'down'" class="size-3.5" />
        {{ formatPercent(delta, locale) }}
      </span>
      <span class="text-muted-foreground">{{ t('dashboard.stats.vsLastPeriod') }}</span>
    </div>
  </Card>
</template>
