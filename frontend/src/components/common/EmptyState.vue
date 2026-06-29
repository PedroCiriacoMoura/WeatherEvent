<script setup lang="ts">
import type { Component } from 'vue'
import { Inbox } from '@lucide/vue'

withDefaults(
  defineProps<{
    icon?: Component
    title: string
    description?: string
  }>(),
  { icon: undefined, description: '' },
)
</script>

<template>
  <div class="flex flex-col items-center justify-center gap-3 px-6 py-12 text-center">
    <div class="flex size-12 items-center justify-center rounded-full bg-muted text-muted-foreground">
      <component :is="icon ?? Inbox" class="size-6" aria-hidden="true" />
    </div>
    <div class="space-y-1">
      <p class="text-sm font-medium text-foreground">{{ title }}</p>
      <p v-if="description" class="mx-auto max-w-sm text-sm text-muted-foreground">
        {{ description }}
      </p>
    </div>
    <slot name="action" />
  </div>
</template>
