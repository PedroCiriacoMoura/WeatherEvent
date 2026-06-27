<script setup lang="ts">
import { Moon, Sun } from '@lucide/vue'
import { useI18n } from 'vue-i18n'

import { Button } from '@/components/ui/button'
import { useTheme } from '@/composables/useTheme'

const { t } = useI18n()
const { resolvedTheme, toggleTheme } = useTheme()
</script>

<template>
  <Button
    variant="ghost"
    size="icon"
    type="button"
    :aria-label="t('theme.toggle')"
    :title="t('theme.toggle')"
    @click="toggleTheme"
  >
    <Transition name="theme-icon" mode="out-in">
      <Moon v-if="resolvedTheme === 'light'" key="moon" class="size-4" />
      <Sun v-else key="sun" class="size-4" />
    </Transition>
  </Button>
</template>

<style scoped>
.theme-icon-enter-active,
.theme-icon-leave-active {
  transition:
    opacity 0.15s ease,
    transform 0.15s ease;
}
.theme-icon-enter-from {
  opacity: 0;
  transform: rotate(-90deg) scale(0.6);
}
.theme-icon-leave-to {
  opacity: 0;
  transform: rotate(90deg) scale(0.6);
}
</style>
