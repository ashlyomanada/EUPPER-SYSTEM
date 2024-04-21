<template>
  <div
    style="
      padding-top: 2rem;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 1rem;
    "
  >
    <canvas ref="groupedBarChart"></canvas>
    <label for="">PPO Rates</label>
  </div>
</template>

<script>
import Chart from "chart.js/auto";
import axios from "axios";

export default {
  data() {
    return {
      chartData: {
        labels: [], // Empty labels array to be filled dynamically
        datasets: [], // Empty datasets array to be filled dynamically
      },
    };
  },
  mounted() {
    this.getAllRates();
  },
  methods: {
    async getAllRates() {
      try {
        const response = await axios.get("/getAllRates");
        const rates = response.data;

        // Prepare data for chart
        const offices = {}; // Object to store rates for each office

        // Group rates by office
        rates.forEach((rate) => {
          const officeName = rate.offices;
          const month = rate.month;
          const total = parseFloat(rate.total);

          if (!offices[officeName]) {
            offices[officeName] = {
              label: officeName,
              data: [],
              backgroundColor: this.getRandomColor(), // Generate random color for each office
            };
          }

          // Add rate for the corresponding month
          const index = this.chartData.labels.indexOf(month);
          if (index === -1) {
            this.chartData.labels.push(month);
          }
          offices[officeName].data[index] = total;
        });

        // Push each office's data to chart datasets
        Object.values(offices).forEach((office) => {
          this.chartData.datasets.push(office);
        });

        // Render chart after updating chartData
        this.renderChart();
      } catch (error) {
        console.error("Error fetching rates:", error);
      }
    },

    renderChart() {
      const ctx = this.$refs.groupedBarChart.getContext("2d");
      new Chart(ctx, {
        type: "bar",
        data: this.chartData,
        options: {
          indexAxis: "y", // Display bars horizontally
          responsive: true,
          plugins: {
            legend: {
              position: "top", // Position legend on top
            },
          },
          scales: {
            x: {
              stacked: true, // Stack bars on x-axis
            },
            y: {
              stacked: false, // Don't stack bars on y-axis
              beginAtZero: true, // Start y-axis from zero
            },
          },
        },
      });
    },

    getRandomColor() {
      // Function to generate random hex color
      return "#" + Math.floor(Math.random() * 16777215).toString(16);
    },
  },
};
</script>

<style>
/* Optional: Add styles for the chart container */
canvas {
  height: 35vh;
  margin: 0 auto;
  color: var(--dark);
}
</style>
