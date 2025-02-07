<template>
	<VueApexCharts
		ref="chartRef"
		:type="_chartData.type"
		:options="_chartData.options"
		:series="_chartData.series"
		width="100%"
	/>
</template>

<script setup lang="ts">
import ApexCharts from 'apexcharts'
import VueApexCharts from "vue3-apexcharts"
import { useChart } from "./useChart"
import { merge } from 'lodash'
import { computed } from 'vue';

// Define the props
interface ChartData {
  type: string; // 'line', 'bar', 'pie', etc.
  options: ApexCharts.ApexOptions // Chart options
  series: ApexAxisChartSeries | ApexNonAxisChartSeries // Chart data
}

const props = defineProps<{
	type: string,
	options: ApexCharts.ApexOptions
	series: ApexAxisChartSeries | ApexNonAxisChartSeries
	chartData: ChartData
}>()
const {
	defaultChartData
} = useChart()

const _chartData = computed(() => {
	return merge({}, defaultChartData.value, props.chartData)
})
</script>