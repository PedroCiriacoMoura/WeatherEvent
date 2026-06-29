<script setup lang="ts">
import { computed } from 'vue'
import type { ApexOptions } from 'apexcharts'
import { useI18n } from 'vue-i18n'
import { Skeleton } from '@/components/ui/skeleton'
import { useChartTheme } from '@/composables/useChartTheme'

const props = withDefaults(
  defineProps<{
    type: 'line' | 'area' | 'bar'
    series: ApexOptions['series']
    options?: ApexOptions
    height?: number
    loading?: boolean
    empty?: boolean
  }>(),
  { height: 280, loading: false, empty: false },
)

const { t } = useI18n()
const { baseOptions } = useChartTheme()

const mergedOptions = computed<ApexOptions>(() => baseOptions(props.options ?? {}))
</script>

<template>
  <div class="w-full" :style="{ minHeight: `${height}px` }">
    <Skeleton v-if="loading" class="w-full" :style="{ height: `${height}px` }" />

    <div
      v-else-if="empty"
      class="flex flex-col items-center justify-center gap-1 text-center"
      :style="{ height: `${height}px` }"
    >
      <p class="text-sm font-medium text-muted-foreground">{{ t('common.empty.title') }}</p>
      <p class="text-xs text-muted-foreground">{{ t('common.empty.chart') }}</p>
    </div>

    <apexchart
      v-else
      :type="type"
      :height="height"
      :series="series"
      :options="mergedOptions"
    />
  </div>
</template>
