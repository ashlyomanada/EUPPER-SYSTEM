<template>
  <div>
    <canvas ref="pieChart"></canvas>
  </div>
</template>

<script>
import { Chart } from "chart.js/auto";

export default {
  data() {
    return {
      chart: null,
    };
  },
  props: {
    chartData: {
      type: Object,
      required: true,
    },
    chartOptions: {
      type: Object,
      required: true,
    },
  },
  mounted() {
    this.renderChart();
  },
  beforeDestroy() {
    this.destroyChart();
  },
  watch: {
    chartData: {
      handler: "updateChart",
      deep: true, // Watch nested properties
    },
  },
  methods: {
    renderChart() {
      // Check if the chart is already initialized
      if (this.chart) {
        this.destroyChart(); // Destroy the existing chart
      }

      // Get the canvas context
      const ctx = this.$refs.pieChart.getContext("2d");

      // Create a new Chart instance
      this.chart = new Chart(ctx, {
        type: "pie",
        data: this.chartData,
        options: this.chartOptions,
      });
    },
    destroyChart() {
      // Check if the chart exists before trying to destroy it
      if (this.chart) {
        this.chart.destroy(); // Destroy the existing chart
        this.chart = null; // Set the reference to null
      }
    },
    updateChart() {
      if (!this.chart) {
        this.chart.data = this.chartData;
        this.chart.update(); // Update the chart with new data
      } else {
        this.renderChart(); // If chart is not yet initialized, create it
      }
    },
  },
};
</script>

<style>
/* Add any specific styles for your pie chart component here */
</style>
