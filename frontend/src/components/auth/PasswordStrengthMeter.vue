<script setup lang="ts">
import { computed, toRef } from 'vue'
import { useI18n } from 'vue-i18n'

import { usePasswordStrength } from '@/composables/usePasswordStrength'

const props = defineProps<{ password: string }>()

const { t } = useI18n()
const { score, labelKey } = usePasswordStrength(toRef(props, 'password'))

const segments = [0, 1, 2, 3]

const barColor = computed(() => {
  switch (score.value) {
    case 0:
    case 1:
      return 'bg-destructive'
    case 2:
      return 'bg-amber-500'
    case 3:
      return 'bg-yellow-500'
    default:
      return 'bg-emerald-500'
  }
})

const labelColor = computed(() => {
  switch (score.value) {
    case 0:
    case 1:
      return 'text-destructive'
    case 2:
      return 'text-amber-600 dark:text-amber-500'
    case 3:
      return 'text-yellow-600 dark:text-yellow-500'
    default:
      return 'text-emerald-600 dark:text-emerald-500'
  }
})
</script>

<template>
  <div v-if="password" class="grid gap-1.5" aria-live="polite">
    <div class="flex gap-1">
      <span
        v-for="i in segments"
        :key="i"
        class="h-1 flex-1 rounded-full transition-colors duration-300"
        :class="i < score ? barColor : 'bg-border'"
      />
    </div>
    <p class="text-xs text-muted-foreground">
      {{ t('auth.strength.label') }}:
      <span class="font-medium" :class="labelColor">{{ t(labelKey) }}</span>
    </p>
  </div>
</template>
