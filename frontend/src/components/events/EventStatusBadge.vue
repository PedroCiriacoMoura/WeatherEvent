<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { Badge, type BadgeVariants } from '@/components/ui/badge'
import type { EventRelation } from '@/types/event'

const props = defineProps<{ status?: EventRelation }>()
const { t } = useI18n()

const variant = computed<BadgeVariants['variant']>(() => {
  switch (props.status?.slug) {
    case 'published':
      return 'success'
    case 'cancelled':
      return 'destructive'
    case 'completed':
      return 'info'
    case 'draft':
    default:
      return 'muted'
  }
})

// Prefer a translated label keyed by slug, falling back to the API-provided name.
const label = computed(() => {
  const slug = props.status?.slug
  if (!slug) return '—'
  const key = `events.status.${slug}`
  const translated = t(key)
  return translated === key ? (props.status?.name ?? slug) : translated
})
</script>

<template>
  <Badge :variant="variant">{{ label }}</Badge>
</template>
