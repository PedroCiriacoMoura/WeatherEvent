<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { X } from '@lucide/vue'
import { useI18n } from 'vue-i18n'

import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { Select, type SelectOption } from '@/components/ui/select'
import { useReferenceStore } from '@/stores/reference'
import type { EventListParams } from '@/types/event'

const filters = defineModel<EventListParams>('filters', { required: true })
const emit = defineEmits<{ reset: [] }>()

const { t } = useI18n()
const reference = useReferenceStore()

onMounted(() => {
  void reference.ensureLoaded()
})

// Bridge undefined (no filter) <-> null (Select's "cleared" value).
function proxy(key: 'category_id' | 'status_id') {
  return computed<string | number | null>({
    get: () => filters.value[key] ?? null,
    set: (value) => {
      filters.value[key] = value === null ? undefined : Number(value)
    },
  })
}

const categoryModel = proxy('category_id')
const statusModel = proxy('status_id')

const outdoorOptions = computed<SelectOption[]>(() => [
  { label: t('events.filters.allTypes'), value: 'all' },
  { label: t('events.filters.outdoor'), value: 'outdoor' },
  { label: t('events.filters.indoor'), value: 'indoor' },
])

const outdoorModel = computed<string | number | null>({
  get: () => {
    if (filters.value.is_outdoor === true) return 'outdoor'
    if (filters.value.is_outdoor === false) return 'indoor'
    return 'all'
  },
  set: (value) => {
    filters.value.is_outdoor = value === 'outdoor' ? true : value === 'indoor' ? false : undefined
  },
})

const hasActiveFilters = computed(
  () =>
    filters.value.category_id !== undefined ||
    filters.value.status_id !== undefined ||
    filters.value.is_outdoor !== undefined ||
    Boolean(filters.value.search),
)

const categoryOptions = computed<SelectOption[]>(() => [
  { label: t('events.filters.allCategories'), value: 'all' },
  ...reference.categoryOptions,
])
const statusOptions = computed<SelectOption[]>(() => [
  { label: t('events.filters.allStatuses'), value: 'all' },
  ...reference.statusOptions,
])

// Map the synthetic "all" sentinel back to a cleared filter.
function onCategory(value: string | number | null) {
  categoryModel.value = value === 'all' ? null : value
}
function onStatus(value: string | number | null) {
  statusModel.value = value === 'all' ? null : value
}
</script>

<template>
  <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="grid gap-1.5">
      <Label class="text-xs text-muted-foreground">{{ t('events.fields.category') }}</Label>
      <Select
        :model-value="categoryModel ?? 'all'"
        :options="categoryOptions"
        @update:model-value="onCategory"
      />
    </div>

    <div class="grid gap-1.5">
      <Label class="text-xs text-muted-foreground">{{ t('events.fields.status') }}</Label>
      <Select
        :model-value="statusModel ?? 'all'"
        :options="statusOptions"
        @update:model-value="onStatus"
      />
    </div>

    <div class="grid gap-1.5">
      <Label class="text-xs text-muted-foreground">{{ t('events.fields.type') }}</Label>
      <Select v-model="outdoorModel" :options="outdoorOptions" />
    </div>

    <div class="flex items-end">
      <Button
        v-if="hasActiveFilters"
        variant="ghost"
        size="sm"
        class="text-muted-foreground"
        @click="emit('reset')"
      >
        <X class="size-4" />
        {{ t('events.filters.clear') }}
      </Button>
    </div>
  </div>
</template>
