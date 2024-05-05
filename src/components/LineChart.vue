<template>
  <div>
    <line-chart :chart-data="data" :options="options"></line-chart>
  </div>
</template>

<script>
import { Line } from "vue-chartjs";

export default {
  extends: Line,
  data() {
    return {
      data: {
        labels: [
          "January",
          "February",
          "March",
          "April",
          "May",
          "June",
          "July",
        ],
        datasets: [
          {
            label: "Data Set 1",
            borderColor: "rgb(75, 192, 192)",
            data: [65, 59, 80, 81, 56, 55, 40],
          },
          {
            label: "Data Set 2",
            borderColor: "rgb(255, 99, 132)",
            data: [28, 48, 40, 19, 86, 27, 90],
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          yAxes: [
            {
              ticks: {
                beginAtZero: true,
              },
            },
          ],
        },
      },
    };
  },
  mounted() {
    // Ensure to only call renderChart once after the component is mounted
    this.renderChart(this.data, this.options);
  },
};
</script>

<style scoped>
/* Add any custom styles here */
</style>
