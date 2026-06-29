<script setup lang="ts">
import { computed } from 'vue'
import { Activity } from '@lucide/vue'
import { useI18n } from 'vue-i18n'

import ErrorState from '@/components/common/ErrorState.vue'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Progress } from '@/components/ui/progress'
import { Skeleton } from '@/components/ui/skeleton'
import { ratingBadgeVariant, ratingIndicatorClass } from '@/lib/health'
import type { HealthScore } from '@/types/weather'

const props = defineProps<{
  health: HealthScore | null
  loading?: boolean
  error?: string | null
}>()

const emit = defineEmits<{ retry: [] }>()
const { t } = useI18n()

const factors = computed(() => {
  if (!props.health) return []
  return (
    [
      ['temperature', props.health.factors.temperature],
      ['wind', props.health.factors.wind],
      ['precipitation', props.health.factors.precipitation],
      ['airQuality', props.health.factors.air_quality],
    ] as const
  ).map(([key, penalty]) => ({ key, penalty }))
})
</script>

<template>
  <Card class="gap-4">
    <CardHeader class="flex flex-row items-center gap-2 pb-0">
      <Activity class="size-4 text-muted-foreground" />
      <CardTitle class="text-base">{{ t('events.detail.healthScore') }}</CardTitle>
    </CardHeader>
    <CardContent>
      <ErrorState v-if="error" :message="error" @retry="emit('retry')" />

      <div v-else-if="loading" class="space-y-4">
        <Skeleton class="h-10 w-24" />
        <Skeleton class="h-2 w-full" />
      </div>

      <div v-else-if="health" class="space-y-5">
        <div class="flex items-end justify-between">
          <div class="flex items-baseline gap-1">
            <span class="text-4xl font-semibold tracking-tight tabular-nums">{{ health.score }}</span>
            <span class="text-sm text-muted-foreground">/ 100</span>
          </div>
          <Badge :variant="ratingBadgeVariant(health.rating)">
            {{ t(`events.rating.${health.rating}`) }}
          </Badge>
        </div>

        <Progress :value="health.score" :indicator-class="ratingIndicatorClass(health.rating)" />

        <dl class="grid grid-cols-2 gap-3 pt-1">
          <div v-for="factor in factors" :key="factor.key" class="rounded-lg border bg-muted/30 px-3 py-2">
            <dt class="text-xs text-muted-foreground">{{ t(`events.factors.${factor.key}`) }}</dt>
            <dd class="text-sm font-medium tabular-nums">
              −{{ factor.penalty }} {{ t('events.detail.points') }}
            </dd>
          </div>
        </dl>
      </div>
    </CardContent>
  </Card>
</template>
