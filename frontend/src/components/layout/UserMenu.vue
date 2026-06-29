<script setup lang="ts">
import { LogOut, User as UserIcon } from '@lucide/vue'
import {
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuPortal,
  DropdownMenuRoot,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from 'reka-ui'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'

import { Avatar } from '@/components/ui/avatar'
import { toast } from '@/components/ui/sonner'
import { useAuthStore } from '@/stores/auth'
import { useNotificationsStore } from '@/stores/notifications'

const { t } = useI18n()
const router = useRouter()
const auth = useAuthStore()
const notifications = useNotificationsStore()

async function signOut(): Promise<void> {
  await auth.logout()
  notifications.reset()
  toast.success(t('auth.toast.signedOut'))
  await router.push({ name: 'login' })
}
</script>

<template>
  <DropdownMenuRoot>
    <DropdownMenuTrigger as-child>
      <button
        type="button"
        class="flex items-center gap-2 rounded-full outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background"
        :aria-label="t('auth.profile.title')"
      >
        <Avatar :initials="auth.initials" />
      </button>
    </DropdownMenuTrigger>
    <DropdownMenuPortal>
      <DropdownMenuContent
        :side-offset="8"
        align="end"
        class="z-50 min-w-56 overflow-hidden rounded-lg border bg-popover p-1 text-popover-foreground shadow-md data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95 data-[state=closed]:zoom-out-95"
      >
        <DropdownMenuLabel class="px-2 py-1.5">
          <p class="truncate text-sm font-medium">{{ auth.user?.name }}</p>
          <p class="truncate text-xs text-muted-foreground">{{ auth.user?.email }}</p>
        </DropdownMenuLabel>
        <DropdownMenuSeparator class="my-1 h-px bg-border" />
        <DropdownMenuItem
          class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-sm outline-none transition-colors select-none focus:bg-accent focus:text-accent-foreground"
          @select="router.push({ name: 'profile' })"
        >
          <UserIcon class="size-4" />
          {{ t('nav.profile') }}
        </DropdownMenuItem>
        <DropdownMenuItem
          class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-sm text-destructive outline-none transition-colors select-none focus:bg-destructive/10 focus:text-destructive"
          @select="signOut"
        >
          <LogOut class="size-4" />
          {{ t('auth.profile.signOut') }}
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenuPortal>
  </DropdownMenuRoot>
</template>
