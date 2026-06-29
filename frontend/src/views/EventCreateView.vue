<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'

import PageHeader from '@/components/common/PageHeader.vue'
import EventForm from '@/components/events/EventForm.vue'
import { Card, CardContent } from '@/components/ui/card'
import { toast } from '@/components/ui/sonner'
import { createEvent } from '@/services/events'
import { parseApiError } from '@/lib/api-error'
import type { EventPayload } from '@/types/event'

const { t } = useI18n()
const router = useRouter()

const submitting = ref(false)
const serverErrors = ref<Record<string, string>>({})

async function onSubmit(payload: EventPayload): Promise<void> {
  submitting.value = true
  serverErrors.value = {}
  try {
    const event = await createEvent(payload)
    toast.success(t('events.toast.created'))
    await router.push({ name: 'event-detail', params: { id: event.id } })
  } catch (error) {
    const parsed = parseApiError(error)
    serverErrors.value = parsed.fieldErrors
    toast.error(parsed.message || t('events.toast.createError'))
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="space-y-6">
    <PageHeader :title="t('events.create.title')" :description="t('events.create.subtitle')" />
    <Card>
      <CardContent>
        <EventForm
          mode="create"
          :submitting="submitting"
          :server-errors="serverErrors"
          @submit="onSubmit"
          @cancel="router.push({ name: 'events' })"
        />
      </CardContent>
    </Card>
  </div>
</template>
