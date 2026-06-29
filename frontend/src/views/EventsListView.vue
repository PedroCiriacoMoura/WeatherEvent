<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { CalendarPlus, CalendarX } from '@lucide/vue'
import { RouterLink, useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'

import EmptyState from '@/components/common/EmptyState.vue'
import ErrorState from '@/components/common/ErrorState.vue'
import PageHeader from '@/components/common/PageHeader.vue'
import SearchInput from '@/components/common/SearchInput.vue'
import EventFilters from '@/components/events/EventFilters.vue'
import EventsTable from '@/components/events/EventsTable.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Pagination } from '@/components/ui/pagination'
import { useDebouncedRef } from '@/composables/useDebouncedRef'
import { useEventsList } from '@/composables/useEventsList'

const { t } = useI18n()
const route = useRoute()

const { items, meta, loading, error, filters, isEmpty, fetch, setPage, setSort, resetFilters } =
  useEventsList({ sort: 'starts_at', direction: 'asc', perPage: 10 })

const search = ref(typeof route.query.search === 'string' ? route.query.search : '')
const debouncedSearch = useDebouncedRef(search, 350)
watch(debouncedSearch, (value) => {
  filters.search = value
})

function onReset(): void {
  search.value = ''
  resetFilters()
}

onMounted(() => {
  if (search.value) filters.search = search.value
})
</script>

<template>
  <div class="space-y-6">
    <PageHeader :title="t('events.list.title')" :description="t('events.list.subtitle')">
      <template #actions>
        <Button as-child>
          <RouterLink :to="{ name: 'event-create' }">
            <CalendarPlus class="size-4" />
            {{ t('events.list.create') }}
          </RouterLink>
        </Button>
      </template>
    </PageHeader>

    <Card class="gap-4">
      <CardContent class="space-y-4">
        <SearchInput v-model="search" :placeholder="t('events.list.searchPlaceholder')" />
        <EventFilters v-model:filters="filters" @reset="onReset" />
      </CardContent>

      <CardContent class="px-0">
        <ErrorState v-if="error" :message="error" @retry="fetch" />
        <EmptyState
          v-else-if="isEmpty"
          :icon="CalendarX"
          :title="t('events.list.emptyTitle')"
          :description="t('events.list.emptyDescription')"
        >
          <template #action>
            <Button as-child variant="outline" size="sm">
              <RouterLink :to="{ name: 'event-create' }">
                <CalendarPlus class="size-4" />
                {{ t('events.list.create') }}
              </RouterLink>
            </Button>
          </template>
        </EmptyState>
        <template v-else>
          <EventsTable
            :items="items"
            :loading="loading"
            :sort-field="filters.sort"
            :sort-direction="filters.direction"
            @sort="setSort"
          />
          <div
            v-if="meta"
            class="flex flex-col items-center justify-between gap-3 px-4 pt-4 sm:flex-row"
          >
            <p class="text-sm text-muted-foreground">
              {{ t('events.list.resultsCount', { from: meta.from ?? 0, to: meta.to ?? 0, total: meta.total }) }}
            </p>
            <Pagination
              :page="meta.current_page"
              :last-page="meta.last_page"
              @update:page="setPage"
            />
          </div>
        </template>
      </CardContent>
    </Card>
  </div>
</template>
