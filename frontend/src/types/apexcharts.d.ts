import type VueApexCharts from 'vue3-apexcharts'

declare module 'vue' {
  export interface GlobalComponents {
    apexchart: typeof VueApexCharts
  }
}

export {}
