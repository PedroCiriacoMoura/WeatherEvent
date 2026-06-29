<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = withDefaults(
  defineProps<{
    initials?: string
    class?: HTMLAttributes['class']
  }>(),
  { initials: '' },
)
</script>

<template>
  <span
    data-slot="avatar"
    :class="
      cn(
        'inline-flex size-9 shrink-0 select-none items-center justify-center rounded-full bg-primary text-xs font-semibold text-primary-foreground',
        props.class,
      )
    "
  >
    <slot>{{ props.initials }}</slot>
  </span>
</template>
