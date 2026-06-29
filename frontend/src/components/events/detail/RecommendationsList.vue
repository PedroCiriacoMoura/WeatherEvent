<script setup lang="ts">
import { Check, Lightbulb } from '@lucide/vue'
import { useI18n } from 'vue-i18n'

import ErrorState from '@/components/common/ErrorState.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Skeleton } from '@/components/ui/skeleton'
import type { Recommendation } from '@/types/weather'

defineProps<{
  recommendation: Recommendation | null
  loading?: boolean
  error?: string | null
}>()

const emit = defineEmits<{ retry: [] }>()
const { t } = useI18n()
</script>

<template>
  <Card class="gap-4">
    <CardHeader class="flex flex-row items-center gap-2 pb-0">
      <Lightbulb class="size-4 text-muted-foreground" />
      <CardTitle class="text-base">{{ t('events.detail.recommendations') }}</CardTitle>
    </CardHeader>
    <CardContent>
      <ErrorState v-if="error" :message="error" @retry="emit('retry')" />

      <div v-else-if="loading" class="space-y-2">
        <Skeleton class="h-4 w-full" />
        <Skeleton class="h-4 w-3/4" />
        <Skeleton class="h-4 w-5/6" />
      </div>

      <div v-else-if="recommendation" class="space-y-4">
        <p class="text-sm text-muted-foreground">{{ recommendation.summary }}</p>
        <ul class="space-y-2.5">
          <li
            v-for="(item, index) in recommendation.items"
            :key="index"
            class="flex items-start gap-2.5 text-sm"
          >
            <span class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
              <Check class="size-3" />
            </span>
            <span>{{ item }}</span>
          </li>
        </ul>
      </div>
    </CardContent>
  </Card>
</template>
