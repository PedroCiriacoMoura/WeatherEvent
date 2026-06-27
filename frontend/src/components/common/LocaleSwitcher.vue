<script setup lang="ts">
import { Check, Languages } from '@lucide/vue'
import {
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuPortal,
  DropdownMenuRoot,
  DropdownMenuTrigger,
} from 'reka-ui'
import { useI18n } from 'vue-i18n'

import { Button } from '@/components/ui/button'
import { SUPPORTED_LOCALES, setLocale, type SupportedLocale } from '@/i18n'
import { cn } from '@/lib/utils'

const { t, locale } = useI18n()

function select(value: SupportedLocale): void {
  setLocale(value)
}
</script>

<template>
  <DropdownMenuRoot>
    <DropdownMenuTrigger as-child>
      <Button variant="ghost" size="icon" type="button" :aria-label="t('language.label')">
        <Languages class="size-4" />
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuPortal>
      <DropdownMenuContent
        :side-offset="8"
        align="end"
        class="z-50 min-w-40 overflow-hidden rounded-lg border bg-popover p-1 text-popover-foreground shadow-md data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95 data-[state=closed]:zoom-out-95"
      >
        <DropdownMenuItem
          v-for="loc in SUPPORTED_LOCALES"
          :key="loc"
          class="relative flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-sm outline-none transition-colors select-none focus:bg-accent focus:text-accent-foreground"
          @select="select(loc)"
        >
          <Check :class="cn('size-4', locale === loc ? 'opacity-100' : 'opacity-0')" />
          {{ t(`language.${loc}`) }}
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenuPortal>
  </DropdownMenuRoot>
</template>
