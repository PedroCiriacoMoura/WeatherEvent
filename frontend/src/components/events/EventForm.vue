<script setup lang="ts">
import { reactive, ref, watch } from 'vue'
import { LoaderCircle } from '@lucide/vue'
import { useI18n } from 'vue-i18n'

import FieldShell from '@/components/common/FieldShell.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select } from '@/components/ui/select'
import { useReferenceStore } from '@/stores/reference'
import type { Event, EventPayload } from '@/types/event'

const props = withDefaults(
  defineProps<{
    mode?: 'create' | 'edit'
    initial?: Event | null
    submitting?: boolean
    serverErrors?: Record<string, string>
  }>(),
  { mode: 'create', initial: null, submitting: false },
)

const emit = defineEmits<{ submit: [payload: EventPayload]; cancel: [] }>()

const { t } = useI18n()
const reference = useReferenceStore()
void reference.ensureLoaded()

interface FormState {
  category_id: number | null
  status_id: number | null
  name: string
  description: string
  city: string
  country: string
  latitude: string
  longitude: string
  starts_at: string
  ends_at: string
  timezone: string
  attendees: string
  is_outdoor: boolean
}

function toLocalInput(iso: string | null | undefined): string {
  if (!iso) return ''
  const date = new Date(iso)
  if (Number.isNaN(date.getTime())) return ''
  const pad = (n: number) => String(n).padStart(2, '0')
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`
}

function defaultTimezone(): string {
  try {
    return Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC'
  } catch {
    return 'UTC'
  }
}

function blankState(): FormState {
  return {
    category_id: null,
    status_id: null,
    name: '',
    description: '',
    city: '',
    country: '',
    latitude: '',
    longitude: '',
    starts_at: '',
    ends_at: '',
    timezone: defaultTimezone(),
    attendees: '',
    is_outdoor: false,
  }
}

const form = reactive<FormState>(blankState())
const errors = ref<Partial<Record<keyof FormState, string>>>({})

function hydrate(event: Event | null): void {
  if (!event) {
    Object.assign(form, blankState())
    return
  }
  Object.assign(form, {
    category_id: event.category?.id ?? null,
    status_id: event.status?.id ?? null,
    name: event.name,
    description: event.description ?? '',
    city: event.city,
    country: event.country,
    latitude: event.coordinates ? String(event.coordinates.latitude) : '',
    longitude: event.coordinates ? String(event.coordinates.longitude) : '',
    starts_at: toLocalInput(event.starts_at),
    ends_at: toLocalInput(event.ends_at),
    timezone: event.timezone || defaultTimezone(),
    attendees: event.attendees != null ? String(event.attendees) : '',
    is_outdoor: event.is_outdoor,
  })
}

watch(() => props.initial, (value) => hydrate(value), { immediate: true })

// Merge backend (422) field errors. Map coordinates onto the lat/lng inputs.
watch(
  () => props.serverErrors,
  (server) => {
    if (!server) return
    const mapped: Partial<Record<keyof FormState, string>> = {}
    for (const [key, message] of Object.entries(server)) {
      if (key in form) mapped[key as keyof FormState] = message
      if (key === 'coordinates') mapped.latitude = message
    }
    errors.value = { ...errors.value, ...mapped }
  },
)

function validate(): boolean {
  const next: Partial<Record<keyof FormState, string>> = {}

  if (form.category_id === null) next.category_id = t('events.validation.categoryRequired')
  if (form.status_id === null) next.status_id = t('events.validation.statusRequired')
  if (!form.name.trim()) next.name = t('events.validation.nameRequired')
  else if (form.name.length > 255) next.name = t('events.validation.nameMax')
  if (!form.city.trim()) next.city = t('events.validation.cityRequired')
  if (!/^[A-Za-z]{2}$/.test(form.country.trim()))
    next.country = t('events.validation.countryFormat')

  const lat = Number(form.latitude)
  if (form.latitude === '' || Number.isNaN(lat) || lat < -90 || lat > 90)
    next.latitude = t('events.validation.latitude')

  const lng = Number(form.longitude)
  if (form.longitude === '' || Number.isNaN(lng) || lng < -180 || lng > 180)
    next.longitude = t('events.validation.longitude')

  if (!form.starts_at) next.starts_at = t('events.validation.startsAtRequired')

  if (form.ends_at && form.starts_at && new Date(form.ends_at) < new Date(form.starts_at))
    next.ends_at = t('events.validation.endsAfterStart')

  if (form.attendees !== '') {
    const attendees = Number(form.attendees)
    if (!Number.isInteger(attendees) || attendees < 0)
      next.attendees = t('events.validation.attendees')
  }

  errors.value = next
  return Object.keys(next).length === 0
}

function onSubmit(): void {
  if (!validate()) return

  const payload: EventPayload = {
    category_id: form.category_id as number,
    status_id: form.status_id as number,
    name: form.name.trim(),
    description: form.description.trim() || null,
    city: form.city.trim(),
    country: form.country.trim().toUpperCase(),
    latitude: Number(form.latitude),
    longitude: Number(form.longitude),
    starts_at: new Date(form.starts_at).toISOString(),
    ends_at: form.ends_at ? new Date(form.ends_at).toISOString() : null,
    timezone: form.timezone.trim() || null,
    attendees: form.attendees === '' ? null : Number(form.attendees),
    is_outdoor: form.is_outdoor,
  }

  emit('submit', payload)
}
</script>

<template>
  <form class="grid gap-6" novalidate @submit.prevent="onSubmit">
    <div class="grid gap-6 sm:grid-cols-2">
      <FieldShell
        class="sm:col-span-2"
        :label="t('events.fields.name')"
        :error="errors.name"
        required
        for="event-name"
      >
        <Input id="event-name" v-model="form.name" :aria-invalid="!!errors.name" />
      </FieldShell>

      <FieldShell :label="t('events.fields.category')" :error="errors.category_id" required>
        <Select
          v-model="form.category_id"
          :options="reference.categoryOptions"
          :placeholder="t('events.placeholders.category')"
          :aria-invalid="!!errors.category_id"
        />
      </FieldShell>

      <FieldShell :label="t('events.fields.status')" :error="errors.status_id" required>
        <Select
          v-model="form.status_id"
          :options="reference.statusOptions"
          :placeholder="t('events.placeholders.status')"
          :aria-invalid="!!errors.status_id"
        />
      </FieldShell>

      <FieldShell
        class="sm:col-span-2"
        :label="t('events.fields.description')"
        :error="errors.description"
        for="event-description"
      >
        <textarea
          id="event-description"
          v-model="form.description"
          rows="3"
          class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 dark:bg-input/30"
          :placeholder="t('events.placeholders.description')"
        />
      </FieldShell>

      <FieldShell :label="t('events.fields.city')" :error="errors.city" required for="event-city">
        <Input id="event-city" v-model="form.city" :aria-invalid="!!errors.city" />
      </FieldShell>

      <FieldShell
        :label="t('events.fields.country')"
        :error="errors.country"
        :hint="t('events.hints.country')"
        required
        for="event-country"
      >
        <Input
          id="event-country"
          v-model="form.country"
          maxlength="2"
          class="uppercase"
          :aria-invalid="!!errors.country"
        />
      </FieldShell>

      <FieldShell
        :label="t('events.fields.latitude')"
        :error="errors.latitude"
        required
        for="event-lat"
      >
        <Input
          id="event-lat"
          v-model="form.latitude"
          type="number"
          step="any"
          inputmode="decimal"
          :aria-invalid="!!errors.latitude"
        />
      </FieldShell>

      <FieldShell
        :label="t('events.fields.longitude')"
        :error="errors.longitude"
        required
        for="event-lng"
      >
        <Input
          id="event-lng"
          v-model="form.longitude"
          type="number"
          step="any"
          inputmode="decimal"
          :aria-invalid="!!errors.longitude"
        />
      </FieldShell>

      <FieldShell
        :label="t('events.fields.startsAt')"
        :error="errors.starts_at"
        required
        for="event-starts"
      >
        <Input
          id="event-starts"
          v-model="form.starts_at"
          type="datetime-local"
          :aria-invalid="!!errors.starts_at"
        />
      </FieldShell>

      <FieldShell :label="t('events.fields.endsAt')" :error="errors.ends_at" for="event-ends">
        <Input
          id="event-ends"
          v-model="form.ends_at"
          type="datetime-local"
          :aria-invalid="!!errors.ends_at"
        />
      </FieldShell>

      <FieldShell :label="t('events.fields.timezone')" :error="errors.timezone" for="event-tz">
        <Input id="event-tz" v-model="form.timezone" />
      </FieldShell>

      <FieldShell
        :label="t('events.fields.attendees')"
        :error="errors.attendees"
        for="event-attendees"
      >
        <Input
          id="event-attendees"
          v-model="form.attendees"
          type="number"
          min="0"
          inputmode="numeric"
          :aria-invalid="!!errors.attendees"
        />
      </FieldShell>

      <div class="flex items-center gap-3 sm:col-span-2">
        <input
          id="event-outdoor"
          v-model="form.is_outdoor"
          type="checkbox"
          class="size-4 rounded border-input text-primary accent-primary focus-visible:ring-2 focus-visible:ring-ring"
        />
        <Label for="event-outdoor" class="cursor-pointer">{{ t('events.fields.isOutdoor') }}</Label>
      </div>
    </div>

    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
      <Button type="button" variant="outline" :disabled="submitting" @click="emit('cancel')">
        {{ t('common.cancel') }}
      </Button>
      <Button type="submit" :disabled="submitting">
        <LoaderCircle v-if="submitting" class="size-4 animate-spin" />
        {{ submitting
          ? t('common.saving')
          : mode === 'edit'
            ? t('events.edit.submit')
            : t('events.create.submit') }}
      </Button>
    </div>
  </form>
</template>
