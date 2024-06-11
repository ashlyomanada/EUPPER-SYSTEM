<template>
  <div>
    <canvas ref="chart" width="400" height="400"></canvas>
    <div class="d-flex justify-content-center gap-2">
      <input
        id="year"
        type="number"
        class="text-center"
        v-model="year"
        :placeholder="currentYear"
      />
      <button @click="fetchData" class="btn btn-success">Find</button>
    </div>
    <div v-if="Object.keys(totalsByOffice).length === 0">
      No data found for the selected year.
    </div>
  </div>
</template>

<script>
import Chart from "chart.js/auto";
import axios from "axios";

export default {
  data() {
    return {
      year: new Date().getFullYear().toString(), // Set the default year to the current year
      totalsByOffice: {}, // Initialize totalsByOffice as an empty object
      error: null, // Initialize error to null
    };
  },
  computed: {
    currentYear() {
      return new Date().getFullYear();
    },
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    fetchData() {
      axios
        .get(`/getAllAverageRatesMarinduque/${this.year}`)
        .then((response) => {
          const { totalsByOffice } = response.data;
          if (totalsByOffice) {
            // Only update totalsByOffice if it's not null or undefined
            this.totalsByOffice = totalsByOffice;
            const labels = this.formatLabels(Object.keys(totalsByOffice));
            this.renderChart(labels, Object.values(totalsByOffice));
          } else {
            console.error("Error: totalsByOffice is null or undefined");
            this.error = "No data found for the selected year.";
          }
        })
        .catch((error) => {
          console.error("Error fetching data:", error);
          this.error = "An error occurred while fetching data.";
        });
    },
    renderChart(labels, data) {
      const ctx = this.$refs.chart.getContext("2d");
      if (this.chart) {
        // Destroy the existing chart if it exists
        this.chart.destroy();
      }
      this.chart = new Chart(ctx, {
        type: "bar",
        data: {
          labels: labels,
          datasets: [
            {
              label: "Average Total",
              data: data,
              backgroundColor: [
                "rgba(255, 99, 132, 0.2)", // Red
                "rgba(54, 162, 235, 0.2)", // Blue
                "rgba(255, 206, 86, 0.2)", // Yellow
                "rgba(75, 192, 192, 0.2)", // Green (example color)
              ],
              borderColor: [
                "rgba(255, 99, 132, 1)",
                "rgba(54, 162, 235, 1)",
                "rgba(255, 206, 86, 1)",
                "rgba(75, 192, 192, 1)",
              ],
              borderWidth: 1,
            },
          ],
        },
        options: {
          scales: {
            y: {
              beginAtZero: true,
            },
          },
          plugins: {
            title: {
              display: true,
              text: `Marinduque Offices Average per year`,
              font: {
                size: 16,
              },
              padding: {
                bottom: 10,
              },
            },
          },
        },
      });
    },

    formatLabels(labels) {
      return labels.map((label) => label.replace(/_/g, " "));
    },
  },
};
</script>

<style scoped>
#year {
  color: var(--dark);
  border: 1px solid var(--dark);
}
</style>
