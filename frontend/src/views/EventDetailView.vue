<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { ArrowLeft, Pencil, Trash2 } from '@lucide/vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'

import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import ErrorState from '@/components/common/ErrorState.vue'
import EventStatusBadge from '@/components/events/EventStatusBadge.vue'
import AlertsPanel from '@/components/events/detail/AlertsPanel.vue'
import EventSummary from '@/components/events/detail/EventSummary.vue'
import ForecastList from '@/components/events/detail/ForecastList.vue'
import HealthScoreGauge from '@/components/events/detail/HealthScoreGauge.vue'
import RecommendationsList from '@/components/events/detail/RecommendationsList.vue'
import WeatherPanel from '@/components/events/detail/WeatherPanel.vue'
import { Button } from '@/components/ui/button'
import { Skeleton } from '@/components/ui/skeleton'
import { toast } from '@/components/ui/sonner'
import { useEvent } from '@/composables/useEvent'
import { deleteEvent } from '@/services/events'
import { parseApiError } from '@/lib/api-error'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const id = route.params.id as string

const {
  event,
  weather,
  health,
  recommendations,
  alerts,
  alertsLoading,
  alertsError,
  fetchEvent,
  fetchWeatherBundle,
  fetchAlerts,
  fetchAll,
} = useEvent(id)

onMounted(fetchAll)

const confirmOpen = ref(false)
const deleting = ref(false)

async function onDelete(): Promise<void> {
  deleting.value = true
  try {
    await deleteEvent(id)
    toast.success(t('events.toast.deleted'))
    await router.push({ name: 'events' })
  } catch (error) {
    toast.error(parseApiError(error).message || t('events.toast.deleteError'))
  } finally {
    deleting.value = false
    confirmOpen.value = false
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4">
      <Button variant="ghost" size="sm" class="w-fit -ml-2 text-muted-foreground" as-child>
        <RouterLink :to="{ name: 'events' }">
          <ArrowLeft class="size-4" />
          {{ t('events.detail.back') }}
        </RouterLink>
      </Button>

      <ErrorState
        v-if="event.error.value && !event.data.value"
        :message="event.error.value"
        @retry="fetchEvent"
      />

      <div v-else class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-2">
          <Skeleton v-if="event.loading.value && !event.data.value" class="h-8 w-64" />
          <div v-else class="flex flex-wrap items-center gap-3">
            <h1 class="text-2xl font-semibold tracking-tight text-balance">
              {{ event.data.value?.name }}
            </h1>
            <EventStatusBadge :status="event.data.value?.status" />
          </div>
        </div>

        <div v-if="event.data.value" class="flex items-center gap-2">
          <Button variant="outline" size="sm" as-child>
            <RouterLink :to="{ name: 'event-edit', params: { id } }">
              <Pencil class="size-4" />
              {{ t('events.detail.edit') }}
            </RouterLink>
          </Button>
          <Button variant="outline" size="sm" class="text-destructive" @click="confirmOpen = true">
            <Trash2 class="size-4" />
            {{ t('events.detail.delete') }}
          </Button>
        </div>
      </div>
    </div>

    <!-- Body -->
    <div v-if="event.data.value || event.loading.value" class="grid gap-6 lg:grid-cols-3">
      <div class="space-y-6 lg:col-span-2">
        <EventSummary v-if="event.data.value" :event="event.data.value" />
        <WeatherPanel
          :weather="weather.data.value"
          :loading="weather.loading.value"
          :error="weather.error.value"
          @retry="fetchWeatherBundle"
        />
        <ForecastList
          :forecast="weather.data.value?.forecast ?? []"
          :loading="weather.loading.value"
        />
      </div>

      <div class="space-y-6">
        <HealthScoreGauge
          :health="health.data.value"
          :loading="health.loading.value"
          :error="health.error.value"
          @retry="fetchWeatherBundle"
        />
        <RecommendationsList
          :recommendation="recommendations.data.value"
          :loading="recommendations.loading.value"
          :error="recommendations.error.value"
          @retry="fetchWeatherBundle"
        />
        <AlertsPanel
          :alerts="alerts"
          :loading="alertsLoading"
          :error="alertsError"
          @retry="fetchAlerts"
        />
      </div>
    </div>

    <ConfirmDialog
      v-model:open="confirmOpen"
      :title="t('events.detail.deleteTitle')"
      :description="t('events.detail.deleteConfirm', { name: event.data.value?.name ?? '' })"
      :confirm-label="t('events.detail.delete')"
      destructive
      :loading="deleting"
      @confirm="onDelete"
    />
  </div>
</template>
