<script setup lang="ts">
import { ref } from 'vue'
import { Menu, Search } from '@lucide/vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'

import LocaleSwitcher from '@/components/common/LocaleSwitcher.vue'
import ThemeToggle from '@/components/common/ThemeToggle.vue'
import AppBreadcrumbs from '@/components/layout/AppBreadcrumbs.vue'
import NotificationsMenu from '@/components/layout/NotificationsMenu.vue'
import UserMenu from '@/components/layout/UserMenu.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

defineEmits<{ 'toggle-sidebar': [] }>()

const { t } = useI18n()
const router = useRouter()
const search = ref('')

function submitSearch(): void {
  const term = search.value.trim()
  router.push({ name: 'events', query: term ? { search: term } : {} })
}
</script>

<template>
  <header
    class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-border bg-background/80 px-4 backdrop-blur-md sm:px-6"
  >
    <Button
      variant="ghost"
      size="icon"
      class="lg:hidden"
      :aria-label="t('nav.openMenu')"
      @click="$emit('toggle-sidebar')"
    >
      <Menu class="size-5" />
    </Button>

    <AppBreadcrumbs />

    <div class="flex flex-1 items-center justify-end gap-1.5">
      <form class="relative hidden md:block" role="search" @submit.prevent="submitSearch">
        <Search
          class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
          aria-hidden="true"
        />
        <Input
          v-model="search"
          type="search"
          :placeholder="t('common.searchEvents')"
          :aria-label="t('common.searchEvents')"
          class="h-9 w-48 pl-9 lg:w-64"
        />
      </form>

      <ThemeToggle />
      <LocaleSwitcher />
      <NotificationsMenu />
      <UserMenu />
    </div>
  </header>
</template>
