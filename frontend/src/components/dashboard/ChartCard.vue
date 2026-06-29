<script setup lang="ts">
import type { ApexOptions } from 'apexcharts'

import ErrorState from '@/components/common/ErrorState.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Chart } from '@/components/ui/chart'

withDefaults(
  defineProps<{
    title: string
    type: 'line' | 'area' | 'bar'
    series: ApexOptions['series']
    options?: ApexOptions
    loading?: boolean
    error?: string | null
    empty?: boolean
    height?: number
  }>(),
  { loading: false, error: null, empty: false, height: 280 },
)

const emit = defineEmits<{ retry: [] }>()
</script>

<template>
  <Card class="gap-4">
    <CardHeader class="pb-0">
      <CardTitle class="text-base">{{ title }}</CardTitle>
    </CardHeader>
    <CardContent>
      <ErrorState v-if="error" :message="error" @retry="emit('retry')" />
      <Chart
        v-else
        :type="type"
        :series="series"
        :options="options"
        :height="height"
        :loading="loading"
        :empty="empty"
      />
    </CardContent>
  </Card>
</template>
