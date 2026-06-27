<script setup lang="ts">
import { Check, CloudSun } from '@lucide/vue'
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'

import LocaleSwitcher from '@/components/common/LocaleSwitcher.vue'
import ThemeToggle from '@/components/common/ThemeToggle.vue'

const { t } = useI18n()

const features = ['auth.brand.feature1', 'auth.brand.feature2', 'auth.brand.feature3']
const year = new Date().getFullYear()
</script>

<template>
  <div class="grid min-h-screen lg:grid-cols-2">
    <!-- Form column -->
    <div class="relative flex flex-col px-6 py-8 sm:px-10 lg:px-16">
      <header class="flex items-center justify-between">
        <RouterLink
          to="/"
          class="flex items-center gap-2 font-semibold tracking-tight transition-opacity hover:opacity-80"
        >
          <CloudSun class="size-5" />
          <span>{{ t('app.name') }}</span>
        </RouterLink>
        <div class="flex items-center gap-1">
          <ThemeToggle />
          <LocaleSwitcher />
        </div>
      </header>

      <main class="flex flex-1 items-center justify-center py-12">
        <div class="w-full max-w-sm">
          <div class="mb-8 space-y-2">
            <h1 class="text-2xl font-semibold tracking-tight">
              <slot name="title" />
            </h1>
            <p class="text-sm text-balance text-muted-foreground">
              <slot name="description" />
            </p>
          </div>

          <slot />

          <div class="mt-8">
            <slot name="footer" />
          </div>
        </div>
      </main>

      <footer class="text-center text-xs text-muted-foreground lg:text-left">
        © {{ year }} {{ t('app.name') }}
      </footer>
    </div>

    <!-- Brand column -->
    <aside class="relative hidden overflow-hidden bg-zinc-950 lg:block">
      <div
        class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(56,189,248,0.18),transparent_45%),radial-gradient(circle_at_bottom_right,rgba(168,85,247,0.16),transparent_45%)]"
      />
      <div
        class="absolute inset-0 opacity-[0.04] [background-image:linear-gradient(white_1px,transparent_1px),linear-gradient(90deg,white_1px,transparent_1px)] [background-size:32px_32px]"
      />
      <div class="absolute -top-10 -left-20 size-72 rounded-full bg-sky-500/20 blur-3xl" />
      <div class="absolute right-[-4rem] bottom-10 size-80 rounded-full bg-violet-500/20 blur-3xl" />

      <div class="relative flex h-full flex-col justify-between p-12 text-white xl:p-16">
        <div class="flex items-center gap-2 text-sm font-medium text-white/80">
          <CloudSun class="size-5" />
          {{ t('app.name') }}
        </div>

        <div class="space-y-6">
          <h2 class="max-w-md text-3xl leading-tight font-semibold tracking-tight text-balance xl:text-4xl">
            {{ t('auth.brand.headline') }}
          </h2>
          <p class="max-w-md text-white/70">{{ t('auth.brand.subheadline') }}</p>
          <ul class="space-y-3">
            <li
              v-for="feature in features"
              :key="feature"
              class="flex items-center gap-3 text-sm text-white/85"
            >
              <span class="flex size-5 shrink-0 items-center justify-center rounded-full bg-white/10">
                <Check class="size-3" />
              </span>
              {{ t(feature) }}
            </li>
          </ul>
        </div>

        <p class="text-sm text-white/50">{{ t('auth.brand.footer') }}</p>
      </div>
    </aside>
  </div>
</template>
