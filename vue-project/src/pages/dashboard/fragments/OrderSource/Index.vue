<template>
  <DashboardCard
    title="Order Source"
    :Key="chartKey"
    @dateChange="loadOrderSourceData"
  >
    <Loader
      :active="isLoading"
      class="bg-white/90 rounded-full p-[2px] absolute inset-1/2 -translate-x-1/2"
    />
    <div class="-ml-3 -mr-3 h-[320px]">
      <Chart.Native :chartData="chartData" width="100%" height="100%" />
    </div>
  </DashboardCard>
</template>

<script setup lang="ts">
import { Chart, Loader } from "@components";
import { computed } from "vue";
import { useOrderSource } from "./useOrderSource.js";
import DashboardCard from "../DashboardCard.vue";

const { chartKey, isLoading, orderSourceData, loadOrderSourceData } =
  useOrderSource();

const chartData = computed(() => {
  return {
    type: "polarArea",
    options: {
      xaxis: {
        categories: orderSourceData.value?.categories || [],
      },
      colors: ["#39c1a0"],
      legend: {
        show: true,
        formatter: function(seriesName, opts) {
          // Access the labels using opts and print them
          const labelIndex = opts.seriesIndex; // Get the current series index
          const label = opts.w.globals.labels[labelIndex]; // Get the corresponding label
          return label; // Print the label instead of seriesName
        },
      },
      tooltip: {
        enabled: true, // Ensure tooltips are enabled
        custom: function({ series, seriesIndex, dataPointIndex, w }) {
          // Access the relevant data
          const label = w.globals.labels[seriesIndex]; // Get the corresponding label
          const value = series[seriesIndex]; // Get the series value
          // Return the HTML content for the tooltip
          return `<div style="padding: 8px; background: #fff; border: 1px solid #ccc; color: black; border-radius: 4px;">
                    <strong>${label.replace('-', ' ')}</strong>: ${value}
                  </div>`;
        },
      },
    },
    series: orderSourceData.value?.series?.length
      ? orderSourceData.value?.series[0].data
      : [],
  };
});
</script>
