<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { CalendarDays, CloudSun, LoaderCircle, LogOut, Mail } from '@lucide/vue'

import LocaleSwitcher from '@/components/common/LocaleSwitcher.vue'
import ThemeToggle from '@/components/common/ThemeToggle.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card'
import { toast } from '@/components/ui/sonner'
import { parseApiError } from '@/lib/api-error'
import { useAuthStore } from '@/stores/auth'

const { t, locale } = useI18n()
const router = useRouter()
const auth = useAuthStore()

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

const memberSince = computed(() => {
  if (!auth.user?.created_at) return ''
  return new Intl.DateTimeFormat(locale.value, {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  }).format(new Date(auth.user.created_at))
})

async function onLogout(): Promise<void> {
  loggingOut.value = true
  try {
    await auth.logout()
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
  <div class="min-h-screen bg-muted/30">
    <header class="border-b bg-background">
      <div class="mx-auto flex h-16 max-w-5xl items-center justify-between px-6">
        <div class="flex items-center gap-2 font-semibold tracking-tight">
          <CloudSun class="size-5" />
          <span>{{ t('app.name') }}</span>
        </div>
        <div class="flex items-center gap-1">
          <ThemeToggle />
          <LocaleSwitcher />
        </div>
      </div>
    </header>

    <main class="mx-auto max-w-5xl px-6 py-10 sm:py-16">
      <div class="mx-auto max-w-md">
        <div class="mb-6 space-y-1">
          <h1 class="text-2xl font-semibold tracking-tight">{{ t('auth.profile.title') }}</h1>
          <p class="text-sm text-muted-foreground">{{ t('auth.profile.subtitle') }}</p>
        </div>

        <Card v-if="auth.user">
          <CardHeader>
            <div class="flex items-center gap-4">
              <div
                class="flex size-16 shrink-0 items-center justify-center rounded-full bg-primary text-lg font-semibold text-primary-foreground"
                aria-hidden="true"
              >
                {{ auth.initials }}
              </div>
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
              <div class="size-16 shrink-0 animate-pulse rounded-full bg-muted" />
              <div class="grid gap-2">
                <div class="h-4 w-32 animate-pulse rounded bg-muted" />
                <div class="h-3 w-40 animate-pulse rounded bg-muted" />
              </div>
            </div>
          </CardHeader>
          <CardContent class="grid gap-3">
            <div class="h-14 animate-pulse rounded-lg bg-muted" />
            <div class="h-14 animate-pulse rounded-lg bg-muted" />
          </CardContent>
        </Card>
      </div>
    </main>
  </div>
</template>
