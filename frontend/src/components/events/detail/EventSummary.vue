<script setup lang="ts">
import { CalendarDays, CalendarRange, MapPin, Tag, Users } from '@lucide/vue'
import { useI18n } from 'vue-i18n'

import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { formatDateTime, formatNumber } from '@/lib/format'
import type { Event } from '@/types/event'

defineProps<{ event: Event }>()

const { t, locale } = useI18n()
</script>

<template>
  <Card class="gap-4">
    <CardHeader class="pb-0">
      <CardTitle class="text-base">{{ t('events.detail.summary') }}</CardTitle>
    </CardHeader>
    <CardContent class="space-y-5">
      <p v-if="event.description" class="text-sm leading-relaxed text-muted-foreground">
        {{ event.description }}
      </p>

      <dl class="grid gap-4 sm:grid-cols-2">
        <div class="flex items-start gap-3">
          <MapPin class="mt-0.5 size-4 shrink-0 text-muted-foreground" />
          <div>
            <dt class="text-xs text-muted-foreground">{{ t('events.fields.location') }}</dt>
            <dd class="text-sm font-medium">{{ event.city }}, {{ event.country }}</dd>
          </div>
        </div>

        <div class="flex items-start gap-3">
          <Tag class="mt-0.5 size-4 shrink-0 text-muted-foreground" />
          <div>
            <dt class="text-xs text-muted-foreground">{{ t('events.fields.category') }}</dt>
            <dd class="text-sm font-medium">{{ event.category?.name ?? '—' }}</dd>
          </div>
        </div>

        <div class="flex items-start gap-3">
          <CalendarDays class="mt-0.5 size-4 shrink-0 text-muted-foreground" />
          <div>
            <dt class="text-xs text-muted-foreground">{{ t('events.fields.startsAt') }}</dt>
            <dd class="text-sm font-medium">{{ formatDateTime(event.starts_at, locale) }}</dd>
          </div>
        </div>

        <div class="flex items-start gap-3">
          <CalendarRange class="mt-0.5 size-4 shrink-0 text-muted-foreground" />
          <div>
            <dt class="text-xs text-muted-foreground">{{ t('events.fields.endsAt') }}</dt>
            <dd class="text-sm font-medium">
              {{ event.ends_at ? formatDateTime(event.ends_at, locale) : '—' }}
            </dd>
          </div>
        </div>

        <div class="flex items-start gap-3">
          <Users class="mt-0.5 size-4 shrink-0 text-muted-foreground" />
          <div>
            <dt class="text-xs text-muted-foreground">{{ t('events.fields.attendees') }}</dt>
            <dd class="text-sm font-medium tabular-nums">{{ formatNumber(event.attendees, locale) }}</dd>
          </div>
        </div>

        <div class="flex items-start gap-3">
          <div>
            <dt class="text-xs text-muted-foreground">{{ t('events.fields.type') }}</dt>
            <dd class="mt-1">
              <Badge variant="outline">
                {{ event.is_outdoor ? t('events.filters.outdoor') : t('events.filters.indoor') }}
              </Badge>
            </dd>
          </div>
        </div>
      </dl>
    </CardContent>
  </Card>
</template>
