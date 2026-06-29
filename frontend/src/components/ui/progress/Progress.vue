<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = withDefaults(
  defineProps<{
    /** Current value, 0–`max`. */
    value?: number
    max?: number
    /** Tailwind classes for the filled indicator (e.g. a rating color). */
    indicatorClass?: HTMLAttributes['class']
    class?: HTMLAttributes['class']
  }>(),
  { value: 0, max: 100 },
)

const percent = computed(() => {
  if (props.max <= 0) return 0
  return Math.min(100, Math.max(0, (props.value / props.max) * 100))
})
</script>

<template>
  <div
    data-slot="progress"
    role="progressbar"
    :aria-valuenow="props.value"
    :aria-valuemin="0"
    :aria-valuemax="props.max"
    :class="cn('relative h-2 w-full overflow-hidden rounded-full bg-muted', props.class)"
  >
    <div
      class="h-full rounded-full transition-[width] duration-500 ease-out"
      :class="cn('bg-primary', props.indicatorClass)"
      :style="{ width: `${percent}%` }"
    />
  </div>
</template>
