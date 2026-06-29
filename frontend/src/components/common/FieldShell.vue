<script setup lang="ts">
import { CircleAlert } from '@lucide/vue'
import { Label } from '@/components/ui/label'

defineProps<{
  label: string
  error?: string
  hint?: string
  required?: boolean
  for?: string
}>()
</script>

<template>
  <div class="grid gap-2">
    <Label :for="$props.for">
      {{ label }}
      <span v-if="required" class="ml-0.5 text-destructive" aria-hidden="true">*</span>
    </Label>

    <slot />

    <p v-if="hint && !error" class="text-xs text-muted-foreground">{{ hint }}</p>

    <Transition name="field-error">
      <p
        v-if="error"
        role="alert"
        class="flex items-center gap-1.5 text-xs font-medium text-destructive"
      >
        <CircleAlert class="size-3.5 shrink-0" />
        <span>{{ error }}</span>
      </p>
    </Transition>
  </div>
</template>

<style scoped>
.field-error-enter-active,
.field-error-leave-active {
  transition:
    opacity 0.18s ease,
    transform 0.18s ease;
}
.field-error-enter-from,
.field-error-leave-to {
  opacity: 0;
  transform: translateY(-2px);
}
</style>
