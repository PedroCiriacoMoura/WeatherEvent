<script setup lang="ts">
import { computed } from 'vue'
import { ChevronLeft, ChevronRight } from '@lucide/vue'
import { useI18n } from 'vue-i18n'
import { Button } from '@/components/ui/button'

const props = defineProps<{
  page: number
  lastPage: number
}>()

const emit = defineEmits<{ 'update:page': [page: number] }>()

const { t } = useI18n()

/** Compact page list with ellipses, e.g. 1 … 4 5 [6] 7 8 … 20. */
const items = computed<(number | 'ellipsis')[]>(() => {
  const total = props.lastPage
  const current = props.page
  if (total <= 7) {
    return Array.from({ length: total }, (_, i) => i + 1)
  }

  const pages: (number | 'ellipsis')[] = [1]
  const start = Math.max(2, current - 1)
  const end = Math.min(total - 1, current + 1)

  if (start > 2) pages.push('ellipsis')
  for (let i = start; i <= end; i++) pages.push(i)
  if (end < total - 1) pages.push('ellipsis')
  pages.push(total)

  return pages
})

function go(page: number): void {
  if (page < 1 || page > props.lastPage || page === props.page) return
  emit('update:page', page)
}
</script>

<template>
  <nav
    v-if="lastPage > 1"
    class="flex items-center justify-center gap-1"
    :aria-label="t('common.pagination')"
  >
    <Button
      variant="ghost"
      size="icon"
      :disabled="page <= 1"
      :aria-label="t('common.previous')"
      @click="go(page - 1)"
    >
      <ChevronLeft class="size-4" />
    </Button>

    <template v-for="(item, index) in items" :key="`${item}-${index}`">
      <span v-if="item === 'ellipsis'" class="px-2 text-sm text-muted-foreground">…</span>
      <Button
        v-else
        :variant="item === page ? 'default' : 'ghost'"
        size="icon"
        :aria-current="item === page ? 'page' : undefined"
        @click="go(item)"
      >
        {{ item }}
      </Button>
    </template>

    <Button
      variant="ghost"
      size="icon"
      :disabled="page >= lastPage"
      :aria-label="t('common.next')"
      @click="go(page + 1)"
    >
      <ChevronRight class="size-4" />
    </Button>
  </nav>
</template>
