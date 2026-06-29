<script setup lang="ts">
import { onMounted } from 'vue'
import { Bell, CheckCheck } from '@lucide/vue'
import {
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuPortal,
  DropdownMenuRoot,
  DropdownMenuTrigger,
} from 'reka-ui'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'

import { Button } from '@/components/ui/button'
import { formatDateTime } from '@/lib/format'
import { useNotificationsStore } from '@/stores/notifications'

const { t, locale } = useI18n()
const router = useRouter()
const notifications = useNotificationsStore()

onMounted(() => {
  if (!notifications.loaded) void notifications.fetch()
})

async function openAlert(id: number, eventId?: number): Promise<void> {
  await notifications.markRead(id)
  if (eventId) await router.push({ name: 'event-detail', params: { id: eventId } })
}
</script>

<template>
  <DropdownMenuRoot>
    <DropdownMenuTrigger as-child>
      <Button variant="ghost" size="icon" type="button" class="relative" :aria-label="t('alerts.title')">
        <Bell class="size-4" />
        <span
          v-if="notifications.hasUnread"
          class="absolute top-1.5 right-1.5 flex min-w-4 items-center justify-center rounded-full bg-destructive px-1 text-[10px] leading-4 font-semibold text-white"
        >
          {{ notifications.unreadCount > 9 ? '9+' : notifications.unreadCount }}
        </span>
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuPortal>
      <DropdownMenuContent
        :side-offset="8"
        align="end"
        class="z-50 w-80 overflow-hidden rounded-lg border bg-popover p-0 text-popover-foreground shadow-md data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95 data-[state=closed]:zoom-out-95"
      >
        <div class="flex items-center justify-between border-b px-4 py-3">
          <p class="text-sm font-semibold">{{ t('alerts.title') }}</p>
          <button
            v-if="notifications.hasUnread"
            type="button"
            class="flex items-center gap-1 text-xs text-muted-foreground transition-colors hover:text-foreground"
            @click="notifications.markAllRead()"
          >
            <CheckCheck class="size-3.5" />
            {{ t('alerts.markAllRead') }}
          </button>
        </div>

        <div class="max-h-80 overflow-y-auto">
          <p
            v-if="!notifications.alerts.length"
            class="px-4 py-8 text-center text-sm text-muted-foreground"
          >
            {{ t('alerts.empty') }}
          </p>

          <DropdownMenuItem
            v-for="alert in notifications.alerts"
            :key="alert.id"
            class="flex cursor-pointer flex-col items-start gap-1 border-b px-4 py-3 text-sm outline-none transition-colors select-none last:border-0 focus:bg-accent focus:text-accent-foreground"
            @select="openAlert(alert.id, alert.event?.id)"
          >
            <div class="flex w-full items-start gap-2">
              <span
                class="mt-1.5 size-2 shrink-0 rounded-full"
                :class="alert.read_at ? 'bg-transparent' : 'bg-primary'"
              />
              <div class="min-w-0 flex-1">
                <p class="truncate font-medium">{{ alert.type?.name ?? t('alerts.generic') }}</p>
                <p class="text-muted-foreground">{{ alert.message }}</p>
                <p class="mt-0.5 text-xs text-muted-foreground/80">
                  {{ formatDateTime(alert.created_at, locale) }}
                </p>
              </div>
            </div>
          </DropdownMenuItem>
        </div>
      </DropdownMenuContent>
    </DropdownMenuPortal>
  </DropdownMenuRoot>
</template>
