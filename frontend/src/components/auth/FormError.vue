<script setup lang="ts">
import { CircleAlert } from '@lucide/vue'

defineProps<{ message: string }>()
</script>

<template>
  <div
    role="alert"
    class="flex items-start gap-2.5 rounded-lg border border-destructive/30 bg-destructive/10 px-3.5 py-2.5 text-sm text-destructive"
  >
    <CircleAlert class="mt-0.5 size-4 shrink-0" />
    <span>{{ message }}</span>
  </div>
</template>
