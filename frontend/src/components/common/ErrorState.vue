<script setup lang="ts">
import { TriangleAlert } from '@lucide/vue'
import { useI18n } from 'vue-i18n'
import { Button } from '@/components/ui/button'

withDefaults(
  defineProps<{
    title?: string
    message?: string
    retryable?: boolean
  }>(),
  { retryable: true },
)

const emit = defineEmits<{ retry: [] }>()
const { t } = useI18n()
</script>

<template>
  <div class="flex flex-col items-center justify-center gap-3 px-6 py-12 text-center">
    <div class="flex size-12 items-center justify-center rounded-full bg-destructive/10 text-destructive">
      <TriangleAlert class="size-6" aria-hidden="true" />
    </div>
    <div class="space-y-1">
      <p class="text-sm font-medium text-foreground">{{ title ?? t('common.error.title') }}</p>
      <p class="mx-auto max-w-sm text-sm text-muted-foreground">
        {{ message ?? t('common.error.message') }}
      </p>
    </div>
    <Button v-if="retryable" variant="outline" size="sm" @click="emit('retry')">
      {{ t('common.retry') }}
    </Button>
  </div>
</template>
