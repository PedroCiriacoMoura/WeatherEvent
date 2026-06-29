<script setup lang="ts">
import { CalendarClock } from '@lucide/vue'
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'

import EmptyState from '@/components/common/EmptyState.vue'
import ErrorState from '@/components/common/ErrorState.vue'
import EventsTable from '@/components/events/EventsTable.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Pagination } from '@/components/ui/pagination'
import { useEventsList } from '@/composables/useEventsList'

const { t } = useI18n()

const { items, meta, loading, error, filters, isEmpty, fetch, setPage, setSort } = useEventsList({
  baseParams: { starts_after: new Date().toISOString() },
  sort: 'starts_at',
  direction: 'asc',
  perPage: 6,
})
</script>

<template>
  <Card class="gap-4">
    <CardHeader class="flex flex-row items-center justify-between pb-0">
      <CardTitle class="text-base">{{ t('dashboard.upcoming.title') }}</CardTitle>
      <Button variant="ghost" size="sm" as-child>
        <RouterLink :to="{ name: 'events' }">{{ t('dashboard.upcoming.viewAll') }}</RouterLink>
      </Button>
    </CardHeader>
    <CardContent class="px-0 pb-0">
      <ErrorState v-if="error" :message="error" @retry="fetch" />
      <EmptyState
        v-else-if="isEmpty"
        :icon="CalendarClock"
        :title="t('dashboard.upcoming.emptyTitle')"
        :description="t('dashboard.upcoming.emptyDescription')"
      />
      <template v-else>
        <EventsTable
          :items="items"
          :loading="loading"
          :sort-field="filters.sort"
          :sort-direction="filters.direction"
          @sort="setSort"
        />
        <div v-if="meta && meta.last_page > 1" class="px-4 pt-4">
          <Pagination :page="meta.current_page" :last-page="meta.last_page" @update:page="setPage" />
        </div>
      </template>
    </CardContent>
  </Card>
</template>
