<script setup lang="ts">
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const year = new Date().getFullYear()
</script>

<template>
  <footer
    class="flex flex-col items-center justify-between gap-2 border-t border-border px-4 py-4 text-xs text-muted-foreground sm:flex-row sm:px-6"
  >
    <p>© {{ year }} {{ t('app.name') }}</p>
    <p>{{ t('app.tagline') }}</p>
  </footer>
</template>
