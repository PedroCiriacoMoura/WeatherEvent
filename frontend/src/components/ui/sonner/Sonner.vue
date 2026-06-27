<script setup lang="ts">
import { Toaster } from 'vue-sonner'
import 'vue-sonner/style.css'
import { useTheme } from '@/composables/useTheme'

const { resolvedTheme } = useTheme()
</script>

<template>
  <Toaster
    :theme="resolvedTheme"
    position="top-right"
    :rich-colors="true"
    :close-button="true"
    :duration="4000"
    :offset="16"
    class="toaster group"
  />
</template>
