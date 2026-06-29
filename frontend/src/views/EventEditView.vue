<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'

import ErrorState from '@/components/common/ErrorState.vue'
import PageHeader from '@/components/common/PageHeader.vue'
import EventForm from '@/components/events/EventForm.vue'
import { Card, CardContent } from '@/components/ui/card'
import { Skeleton } from '@/components/ui/skeleton'
import { toast } from '@/components/ui/sonner'
import { getEvent, updateEvent } from '@/services/events'
import { parseApiError } from '@/lib/api-error'
import type { Event, EventPayload } from '@/types/event'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const id = route.params.id as string

const event = ref<Event | null>(null)
const loading = ref(true)
const loadError = ref<string | null>(null)
const submitting = ref(false)
const serverErrors = ref<Record<string, string>>({})

async function load(): Promise<void> {
  loading.value = true
  loadError.value = null
  try {
    event.value = await getEvent(id)
  } catch (error) {
    loadError.value = parseApiError(error).message
  } finally {
    loading.value = false
  }
}

onMounted(load)

async function onSubmit(payload: EventPayload): Promise<void> {
  submitting.value = true
  serverErrors.value = {}
  try {
    await updateEvent(id, payload)
    toast.success(t('events.toast.updated'))
    await router.push({ name: 'event-detail', params: { id } })
  } catch (error) {
    const parsed = parseApiError(error)
    serverErrors.value = parsed.fieldErrors
    toast.error(parsed.message || t('events.toast.updateError'))
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="space-y-6">
    <PageHeader :title="t('events.edit.title')" :description="t('events.edit.subtitle')" />
    <Card>
      <CardContent>
        <ErrorState v-if="loadError" :message="loadError" @retry="load" />
        <div v-else-if="loading" class="grid gap-6 sm:grid-cols-2">
          <Skeleton v-for="n in 6" :key="n" class="h-16" :class="n === 1 && 'sm:col-span-2'" />
        </div>
        <EventForm
          v-else
          mode="edit"
          :initial="event"
          :submitting="submitting"
          :server-errors="serverErrors"
          @submit="onSubmit"
          @cancel="router.push({ name: 'event-detail', params: { id } })"
        />
      </CardContent>
    </Card>
  </div>
</template>
