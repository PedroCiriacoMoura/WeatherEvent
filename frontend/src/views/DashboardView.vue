<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { CalendarClock, CalendarDays, Thermometer, TriangleAlert } from '@lucide/vue'
import type { ApexOptions } from 'apexcharts'
import { useI18n } from 'vue-i18n'

import PageHeader from '@/components/common/PageHeader.vue'
import ChartCard from '@/components/dashboard/ChartCard.vue'
import StatCard from '@/components/dashboard/StatCard.vue'
import UpcomingEventsTable from '@/components/dashboard/UpcomingEventsTable.vue'
import { useDashboard } from '@/composables/useDashboard'
import { formatDate, formatTemperature } from '@/lib/format'
import { HEALTH_RATINGS, RISK_COLORS } from '@/lib/health'

const { t, locale } = useI18n()
const { stats, statsLoading, charts, chartsLoading, chartsError, fetchCharts, fetchAll } =
  useDashboard()

onMounted(fetchAll)

function categories(points: { at: string }[]): string[] {
  return points.map((p) => formatDate(p.at, locale.value))
}

const temperatureSeries = computed(() => [
  { name: t('dashboard.charts.temperature'), data: charts.value?.temperature.map((p) => p.value) ?? [] },
])
const temperatureOptions = computed<ApexOptions>(() => ({
  chart: { type: 'line' },
  xaxis: { categories: categories(charts.value?.temperature ?? []) },
  yaxis: { labels: { formatter: (v: number) => `${Math.round(v)}°` } },
  stroke: { width: 3 },
}))

const rainSeries = computed(() => [
  { name: t('dashboard.charts.rain'), data: charts.value?.rain.map((p) => p.value) ?? [] },
])
const rainOptions = computed<ApexOptions>(() => ({
  chart: { type: 'bar' },
  colors: ['#3b82f6'],
  plotOptions: { bar: { borderRadius: 4, columnWidth: '45%' } },
  xaxis: { categories: categories(charts.value?.rain ?? []) },
  yaxis: { labels: { formatter: (v: number) => `${v} mm` } },
}))

const humiditySeries = computed(() => [
  { name: t('dashboard.charts.humidity'), data: charts.value?.humidity.map((p) => p.value) ?? [] },
])
const humidityOptions = computed<ApexOptions>(() => ({
  chart: { type: 'area' },
  colors: ['#06b6d4'],
  fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
  xaxis: { categories: categories(charts.value?.humidity ?? []) },
  yaxis: { max: 100, labels: { formatter: (v: number) => `${Math.round(v)}%` } },
}))

const riskSeries = computed(() =>
  HEALTH_RATINGS.map((rating) => ({
    name: t(`events.rating.${rating}`),
    data: charts.value?.risk.map((p) => p[rating]) ?? [],
  })),
)
const riskOptions = computed<ApexOptions>(() => ({
  chart: { type: 'bar', stacked: true },
  colors: HEALTH_RATINGS.map((r) => RISK_COLORS[r]),
  plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
  xaxis: { categories: categories(charts.value?.risk ?? []) },
}))

const isChartEmpty = (points: unknown[]) => !chartsLoading.value && points.length === 0
</script>

<template>
  <div class="space-y-6">
    <PageHeader :title="t('dashboard.title')" :description="t('dashboard.subtitle')" />

    <!-- Stat cards -->
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <StatCard
        :label="t('dashboard.stats.totalEvents')"
        :value="stats?.total_events ?? 0"
        :icon="CalendarDays"
        :delta="stats?.total_events_delta ?? null"
        :tooltip="t('dashboard.stats.totalEventsHint')"
        :loading="statsLoading"
      />
      <StatCard
        :label="t('dashboard.stats.upcomingEvents')"
        :value="stats?.upcoming_events ?? 0"
        :icon="CalendarClock"
        :delta="stats?.upcoming_events_delta ?? null"
        :tooltip="t('dashboard.stats.upcomingEventsHint')"
        :loading="statsLoading"
      />
      <StatCard
        :label="t('dashboard.stats.criticalEvents')"
        :value="stats?.critical_events ?? 0"
        :icon="TriangleAlert"
        :tooltip="t('dashboard.stats.criticalEventsHint')"
        :loading="statsLoading"
      />
      <StatCard
        :label="t('dashboard.stats.averageTemperature')"
        :value="formatTemperature(stats?.average_temperature, locale)"
        :icon="Thermometer"
        :tooltip="t('dashboard.stats.averageTemperatureHint')"
        :loading="statsLoading"
        :empty="stats?.average_temperature === null || stats?.average_temperature === undefined"
      />
    </div>

    <!-- Charts -->
    <div class="grid gap-4 lg:grid-cols-2">
      <ChartCard
        :title="t('dashboard.charts.temperatureTitle')"
        type="line"
        :series="temperatureSeries"
        :options="temperatureOptions"
        :loading="chartsLoading"
        :error="chartsError"
        :empty="isChartEmpty(charts?.temperature ?? [])"
        @retry="fetchCharts"
      />
      <ChartCard
        :title="t('dashboard.charts.rainTitle')"
        type="bar"
        :series="rainSeries"
        :options="rainOptions"
        :loading="chartsLoading"
        :error="chartsError"
        :empty="isChartEmpty(charts?.rain ?? [])"
        @retry="fetchCharts"
      />
      <ChartCard
        :title="t('dashboard.charts.humidityTitle')"
        type="area"
        :series="humiditySeries"
        :options="humidityOptions"
        :loading="chartsLoading"
        :error="chartsError"
        :empty="isChartEmpty(charts?.humidity ?? [])"
        @retry="fetchCharts"
      />
      <ChartCard
        :title="t('dashboard.charts.riskTitle')"
        type="bar"
        :series="riskSeries"
        :options="riskOptions"
        :loading="chartsLoading"
        :error="chartsError"
        :empty="isChartEmpty(charts?.risk ?? [])"
        @retry="fetchCharts"
      />
    </div>

    <!-- Upcoming events -->
    <UpcomingEventsTable />
  </div>
</template>
