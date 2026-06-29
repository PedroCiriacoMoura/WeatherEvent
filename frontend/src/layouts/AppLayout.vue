<script setup lang="ts">
import { RouterView } from 'vue-router'

import AppFooter from '@/components/layout/AppFooter.vue'
import AppHeader from '@/components/layout/AppHeader.vue'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import { useSidebar } from '@/composables/useSidebar'

const { mobileOpen, openMobile, closeMobile } = useSidebar()
</script>

<template>
  <div class="flex h-screen overflow-hidden bg-background text-foreground">
    <!-- Desktop sidebar -->
    <div class="hidden lg:block">
      <AppSidebar />
    </div>

    <!-- Mobile drawer -->
    <Transition name="drawer">
      <div v-if="mobileOpen" class="fixed inset-0 z-50 lg:hidden">
        <div
          class="absolute inset-0 bg-black/50 backdrop-blur-sm"
          aria-hidden="true"
          @click="closeMobile"
        />
        <div class="absolute inset-y-0 left-0">
          <AppSidebar force-expanded @navigate="closeMobile" />
        </div>
      </div>
    </Transition>

    <!-- Main column -->
    <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
      <AppHeader @toggle-sidebar="openMobile" />

      <main class="flex flex-1 flex-col overflow-y-auto">
        <div class="mx-auto w-full max-w-7xl flex-1 px-4 py-6 sm:px-6 lg:px-8">
          <RouterView v-slot="{ Component, route }">
            <Transition name="route" mode="out-in">
              <component :is="Component" :key="route.name ?? route.path" />
            </Transition>
          </RouterView>
        </div>
        <AppFooter />
      </main>
    </div>
  </div>
</template>

<style scoped>
.route-enter-active,
.route-leave-active {
  transition:
    opacity 0.2s ease,
    transform 0.2s ease;
}
.route-enter-from {
  opacity: 0;
  transform: translateY(6px);
}
.route-leave-to {
  opacity: 0;
}

.drawer-enter-active,
.drawer-leave-active {
  transition: opacity 0.2s ease;
}
.drawer-enter-active .absolute.inset-y-0,
.drawer-leave-active .absolute.inset-y-0 {
  transition: transform 0.25s ease;
}
.drawer-enter-from,
.drawer-leave-to {
  opacity: 0;
}
.drawer-enter-from .absolute.inset-y-0,
.drawer-leave-to .absolute.inset-y-0 {
  transform: translateX(-100%);
}
</style>
