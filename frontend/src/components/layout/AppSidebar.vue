<script setup lang="ts">
import { computed } from 'vue'
import { CloudSun, PanelLeftClose, PanelLeftOpen } from '@lucide/vue'
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'

import { Tooltip } from '@/components/ui/tooltip'
import { useSidebar } from '@/composables/useSidebar'
import { cn } from '@/lib/utils'
import { NAV_ITEMS } from './navigation'

const props = withDefaults(defineProps<{ forceExpanded?: boolean }>(), { forceExpanded: false })
const emit = defineEmits<{ navigate: [] }>()

const { t } = useI18n()
const { collapsed, toggleCollapsed } = useSidebar()

const isCollapsed = computed(() => !props.forceExpanded && collapsed.value)
</script>

<template>
  <aside
    :class="
      cn(
        'flex h-full flex-col border-r border-sidebar-border bg-sidebar text-sidebar-foreground transition-[width] duration-300 ease-in-out',
        isCollapsed ? 'w-16' : 'w-64',
      )
    "
  >
    <!-- Brand -->
    <div class="flex h-16 items-center gap-2 px-4">
      <RouterLink
        :to="{ name: 'dashboard' }"
        class="flex items-center gap-2 overflow-hidden font-semibold tracking-tight transition-opacity hover:opacity-80"
        @click="emit('navigate')"
      >
        <CloudSun class="size-6 shrink-0 text-primary" />
        <span v-if="!isCollapsed" class="truncate">{{ t('app.name') }}</span>
      </RouterLink>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 space-y-1 px-3 py-4" :aria-label="t('nav.primary')">
      <template v-for="item in NAV_ITEMS" :key="item.name">
        <Tooltip v-if="isCollapsed" :delay="100">
          <RouterLink
            :to="{ name: item.name }"
            class="group flex items-center justify-center rounded-lg p-2.5 text-sm font-medium text-muted-foreground transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
            active-class="bg-sidebar-accent text-sidebar-accent-foreground"
            @click="emit('navigate')"
          >
            <component :is="item.icon" class="size-5 shrink-0" />
            <span class="sr-only">{{ t(item.labelKey) }}</span>
          </RouterLink>
          <template #content>{{ t(item.labelKey) }}</template>
        </Tooltip>

        <RouterLink
          v-else
          :to="{ name: item.name }"
          class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-muted-foreground transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
          active-class="bg-sidebar-accent text-sidebar-accent-foreground"
          @click="emit('navigate')"
        >
          <component :is="item.icon" class="size-5 shrink-0" />
          <span class="truncate">{{ t(item.labelKey) }}</span>
        </RouterLink>
      </template>
    </nav>

    <!-- Collapse toggle (desktop only) -->
    <div v-if="!forceExpanded" class="border-t border-sidebar-border p-3">
      <button
        type="button"
        class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-muted-foreground transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
        :class="isCollapsed && 'justify-center'"
        :aria-label="t(isCollapsed ? 'nav.expand' : 'nav.collapse')"
        @click="toggleCollapsed"
      >
        <PanelLeftOpen v-if="isCollapsed" class="size-5 shrink-0" />
        <template v-else>
          <PanelLeftClose class="size-5 shrink-0" />
          <span class="truncate">{{ t('nav.collapse') }}</span>
        </template>
      </button>
    </div>
  </aside>
</template>
