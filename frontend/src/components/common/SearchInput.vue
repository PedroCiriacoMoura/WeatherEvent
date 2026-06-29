<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { Search, X } from '@lucide/vue'
import { useI18n } from 'vue-i18n'
import { Input } from '@/components/ui/input'
import { cn } from '@/lib/utils'

const props = defineProps<{
  placeholder?: string
  class?: HTMLAttributes['class']
}>()

const model = defineModel<string>({ default: '' })
const { t } = useI18n()
</script>

<template>
  <div :class="cn('relative', props.class)">
    <Search
      class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
      aria-hidden="true"
    />
    <Input
      v-model="model"
      type="search"
      :placeholder="placeholder ?? t('common.search')"
      :aria-label="placeholder ?? t('common.search')"
      class="pl-9"
      :class="model ? 'pr-9' : ''"
    />
    <button
      v-if="model"
      type="button"
      class="absolute top-1/2 right-2 flex size-6 -translate-y-1/2 items-center justify-center rounded-md text-muted-foreground transition-colors hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
      :aria-label="t('common.clear')"
      @click="model = ''"
    >
      <X class="size-4" />
    </button>
  </div>
</template>
