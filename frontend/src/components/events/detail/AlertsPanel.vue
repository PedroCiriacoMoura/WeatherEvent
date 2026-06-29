<script setup lang="ts">
import { BellOff } from '@lucide/vue'
import { useI18n } from 'vue-i18n'

import EmptyState from '@/components/common/EmptyState.vue'
import ErrorState from '@/components/common/ErrorState.vue'
import AlertCard from '@/components/events/detail/AlertCard.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Skeleton } from '@/components/ui/skeleton'
import type { Alert } from '@/types/alert'

defineProps<{
  alerts: Alert[]
  loading?: boolean
  error?: string | null
}>()

const emit = defineEmits<{ retry: [] }>()
const { t } = useI18n()
</script>

<template>
  <Card class="gap-4">
    <CardHeader class="pb-0">
      <CardTitle class="text-base">{{ t('events.detail.alerts') }}</CardTitle>
    </CardHeader>
    <CardContent>
      <ErrorState v-if="error" :message="error" @retry="emit('retry')" />

      <div v-else-if="loading" class="space-y-2">
        <Skeleton v-for="n in 2" :key="n" class="h-16 w-full" />
      </div>

      <EmptyState
        v-else-if="!alerts.length"
        :icon="BellOff"
        :title="t('events.alerts.emptyTitle')"
        :description="t('events.alerts.emptyDescription')"
      />

      <div v-else class="space-y-3">
        <AlertCard v-for="alert in alerts" :key="alert.id" :alert="alert" />
      </div>
    </CardContent>
  </Card>
</template>
