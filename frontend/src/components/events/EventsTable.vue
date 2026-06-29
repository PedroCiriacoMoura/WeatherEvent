<script setup lang="ts">
import { ArrowDown, ArrowUp, ChevronsUpDown, MapPin, Users } from '@lucide/vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'

import EventStatusBadge from '@/components/events/EventStatusBadge.vue'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Skeleton } from '@/components/ui/skeleton'
import { formatDateTime, formatNumber } from '@/lib/format'
import type { Event, EventSortField } from '@/types/event'
import type { SortDirection } from '@/types/pagination'

const props = withDefaults(
  defineProps<{
    items: Event[]
    loading?: boolean
    sortField?: EventSortField
    sortDirection?: SortDirection
    sortable?: boolean
    skeletonRows?: number
  }>(),
  { loading: false, sortable: true, skeletonRows: 6 },
)

const emit = defineEmits<{ sort: [field: EventSortField] }>()

const { t, locale } = useI18n()
const router = useRouter()

function onSort(field: EventSortField): void {
  if (props.sortable) emit('sort', field)
}

function open(id: number): void {
  router.push({ name: 'event-detail', params: { id } })
}
</script>

<template>
  <Table>
    <TableHeader>
      <TableRow class="hover:bg-transparent">
        <TableHead>
          <button
            v-if="sortable"
            type="button"
            class="inline-flex items-center gap-1 transition-colors hover:text-foreground"
            @click="onSort('name')"
          >
            {{ t('events.fields.name') }}
            <ArrowUp v-if="sortField === 'name' && sortDirection === 'asc'" class="size-3" />
            <ArrowDown v-else-if="sortField === 'name' && sortDirection === 'desc'" class="size-3" />
            <ChevronsUpDown v-else class="size-3 opacity-50" />
          </button>
          <span v-else>{{ t('events.fields.name') }}</span>
        </TableHead>
        <TableHead>{{ t('events.fields.status') }}</TableHead>
        <TableHead>{{ t('events.fields.location') }}</TableHead>
        <TableHead>
          <button
            v-if="sortable"
            type="button"
            class="inline-flex items-center gap-1 transition-colors hover:text-foreground"
            @click="onSort('starts_at')"
          >
            {{ t('events.fields.startsAt') }}
            <ArrowUp v-if="sortField === 'starts_at' && sortDirection === 'asc'" class="size-3" />
            <ArrowDown
              v-else-if="sortField === 'starts_at' && sortDirection === 'desc'"
              class="size-3"
            />
            <ChevronsUpDown v-else class="size-3 opacity-50" />
          </button>
          <span v-else>{{ t('events.fields.startsAt') }}</span>
        </TableHead>
        <TableHead class="text-right">
          <button
            v-if="sortable"
            type="button"
            class="ml-auto inline-flex items-center gap-1 transition-colors hover:text-foreground"
            @click="onSort('attendees')"
          >
            {{ t('events.fields.attendees') }}
            <ArrowUp v-if="sortField === 'attendees' && sortDirection === 'asc'" class="size-3" />
            <ArrowDown
              v-else-if="sortField === 'attendees' && sortDirection === 'desc'"
              class="size-3"
            />
            <ChevronsUpDown v-else class="size-3 opacity-50" />
          </button>
          <span v-else>{{ t('events.fields.attendees') }}</span>
        </TableHead>
      </TableRow>
    </TableHeader>

    <TableBody>
      <template v-if="loading">
        <TableRow v-for="n in skeletonRows" :key="`sk-${n}`" class="hover:bg-transparent">
          <TableCell><Skeleton class="h-4 w-40" /></TableCell>
          <TableCell><Skeleton class="h-5 w-20 rounded-full" /></TableCell>
          <TableCell><Skeleton class="h-4 w-28" /></TableCell>
          <TableCell><Skeleton class="h-4 w-32" /></TableCell>
          <TableCell><Skeleton class="ml-auto h-4 w-12" /></TableCell>
        </TableRow>
      </template>

      <template v-else>
        <TableRow
          v-for="event in items"
          :key="event.id"
          class="cursor-pointer"
          tabindex="0"
          @click="open(event.id)"
          @keydown.enter="open(event.id)"
        >
          <TableCell class="font-medium text-foreground">
            <span class="block max-w-[16rem] truncate">{{ event.name }}</span>
            <span v-if="event.category" class="text-xs text-muted-foreground">
              {{ event.category.name }}
            </span>
          </TableCell>
          <TableCell><EventStatusBadge :status="event.status" /></TableCell>
          <TableCell class="text-muted-foreground">
            <span class="inline-flex items-center gap-1.5">
              <MapPin class="size-3.5 shrink-0" />
              {{ event.city }}, {{ event.country }}
            </span>
          </TableCell>
          <TableCell class="text-muted-foreground">
            {{ formatDateTime(event.starts_at, locale) }}
          </TableCell>
          <TableCell class="text-right">
            <span class="inline-flex items-center gap-1.5 tabular-nums">
              <Users class="size-3.5 shrink-0 text-muted-foreground" />
              {{ formatNumber(event.attendees, locale) }}
            </span>
          </TableCell>
        </TableRow>
      </template>
    </TableBody>
  </Table>
</template>
