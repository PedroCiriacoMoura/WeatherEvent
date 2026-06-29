<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<{ class?: HTMLAttributes['class'] }>()
</script>

<template>
  <th
    data-slot="table-head"
    :class="
      cn(
        'h-11 px-4 text-left align-middle text-xs font-medium tracking-wide text-muted-foreground uppercase whitespace-nowrap',
        props.class,
      )
    "
  >
    <slot />
  </th>
</template>
