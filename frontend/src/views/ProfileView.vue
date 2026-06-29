<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { CalendarDays, LoaderCircle, LogOut, Mail } from '@lucide/vue'

import PageHeader from '@/components/common/PageHeader.vue'
import { Avatar } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card'
import { Skeleton } from '@/components/ui/skeleton'
import { toast } from '@/components/ui/sonner'
import { formatDate } from '@/lib/format'
import { parseApiError } from '@/lib/api-error'
import { useAuthStore } from '@/stores/auth'
import { useNotificationsStore } from '@/stores/notifications'

const { t, locale } = useI18n()
const router = useRouter()
const auth = useAuthStore()
const notifications = useNotificationsStore()

const loggingOut = ref(false)

onMounted(async () => {
  if (!auth.user) {
    try {
      await auth.fetchMe()
    } catch {
      // The navigation guard redirects unauthenticated users to /login.
    }
  }
})

const memberSince = computed(() => formatDate(auth.user?.created_at, locale.value))

async function onLogout(): Promise<void> {
  loggingOut.value = true
  try {
    await auth.logout()
    notifications.reset()
    toast.success(t('auth.toast.signedOut'))
    await router.push('/login')
  } catch (error) {
    toast.error(parseApiError(error).message || t('auth.toast.genericError'))
  } finally {
    loggingOut.value = false
  }
}
</script>

<template>
  <div class="space-y-6">
    <PageHeader :title="t('auth.profile.title')" :description="t('auth.profile.subtitle')" />

    <div class="max-w-md">
      <Card v-if="auth.user">
        <CardHeader>
          <div class="flex items-center gap-4">
            <Avatar :initials="auth.initials" class="size-16 text-lg" />
            <div class="min-w-0">
              <p class="truncate text-lg font-semibold">{{ auth.user.name }}</p>
              <p class="truncate text-sm text-muted-foreground">{{ auth.user.email }}</p>
            </div>
          </div>
        </CardHeader>

        <CardContent class="grid gap-3">
          <div class="flex items-center gap-3 rounded-lg border bg-background px-4 py-3">
            <Mail class="size-4 shrink-0 text-muted-foreground" />
            <div class="min-w-0">
              <p class="text-xs text-muted-foreground">{{ t('auth.profile.email') }}</p>
              <p class="truncate text-sm font-medium">{{ auth.user.email }}</p>
            </div>
          </div>
          <div class="flex items-center gap-3 rounded-lg border bg-background px-4 py-3">
            <CalendarDays class="size-4 shrink-0 text-muted-foreground" />
            <div class="min-w-0">
              <p class="text-xs text-muted-foreground">{{ t('auth.profile.memberSince') }}</p>
              <p class="truncate text-sm font-medium">{{ memberSince }}</p>
            </div>
          </div>
        </CardContent>

        <CardFooter>
          <Button variant="outline" class="w-full" :disabled="loggingOut" @click="onLogout">
            <LoaderCircle v-if="loggingOut" class="size-4 animate-spin" />
            <LogOut v-else class="size-4" />
            {{ loggingOut ? t('auth.profile.signingOut') : t('auth.profile.signOut') }}
          </Button>
        </CardFooter>
      </Card>

      <Card v-else>
        <CardHeader>
          <div class="flex items-center gap-4">
            <Skeleton class="size-16 rounded-full" />
            <div class="grid gap-2">
              <Skeleton class="h-4 w-32" />
              <Skeleton class="h-3 w-40" />
            </div>
          </div>
        </CardHeader>
        <CardContent class="grid gap-3">
          <Skeleton class="h-14 rounded-lg" />
          <Skeleton class="h-14 rounded-lg" />
        </CardContent>
      </Card>
    </div>
  </div>
</template>
