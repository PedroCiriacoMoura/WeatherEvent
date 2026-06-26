<script setup lang="ts">
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import type { ApexOptions } from 'apexcharts'

import { Button } from '@/components/ui/button'
import { SUPPORTED_LOCALES, setLocale, type SupportedLocale } from '@/i18n'

const { t, locale } = useI18n()

function changeLocale(value: SupportedLocale) {
  setLocale(value)
}

const series = ref([
  {
    name: 'Temperature (°C)',
    data: [18, 21, 25, 27, 24, 22, 19],
  },
])

const chartOptions = computed<ApexOptions>(() => ({
  chart: { toolbar: { show: false } },
  stroke: { curve: 'smooth', width: 3 },
  colors: ['var(--color-chart-1)'],
  dataLabels: { enabled: false },
  xaxis: {
    categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
  },
}))
</script>

<template>
  <main class="mx-auto flex max-w-2xl flex-col gap-6 p-8">
    <header>
      <h1 class="text-2xl font-bold">{{ t('app.name') }}</h1>
      <p class="text-muted-foreground">{{ t('app.tagline') }}</p>
    </header>

    <section class="flex items-center gap-2">
      <span class="text-sm font-medium">{{ t('language.label') }}:</span>
      <Button
        v-for="loc in SUPPORTED_LOCALES"
        :key="loc"
        :variant="locale === loc ? 'default' : 'outline'"
        size="sm"
        @click="changeLocale(loc)"
      >
        {{ t(`language.${loc}`) }}
      </Button>
    </section>

    <section class="rounded-lg border p-4">
      <apexchart type="line" height="280" :options="chartOptions" :series="series" />
    </section>
  </main>
</template>
