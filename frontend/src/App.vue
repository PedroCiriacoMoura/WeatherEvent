<script setup lang="ts">
import { onMounted } from 'vue'
import { RouterView } from 'vue-router'

import { Toaster } from '@/components/ui/sonner'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

onMounted(() => {
  void auth.init()
})
</script>

<template>
  <RouterView v-slot="{ Component, route }">
    <Transition name="route" mode="out-in">
      <component :is="Component" :key="route.name ?? route.path" />
    </Transition>
  </RouterView>

  <Toaster />
</template>

<style>
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
</style>
