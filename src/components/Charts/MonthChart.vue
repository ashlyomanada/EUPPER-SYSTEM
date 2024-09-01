<template>
  <div class="Occidental">
    <canvas ref="chart" width="200" height="200"></canvas>
    <div class="d-flex justify-content-center gap-2">
      <select class="form-control text-center" v-model="month">
        <option value="January">January</option>
        <option value="February">February</option>
        <option value="March">March</option>
        <option value="April">April</option>
        <option value="May">May</option>
        <option value="June">June</option>
        <option value="July">July</option>
        <option value="August">August</option>
        <option value="September">September</option>
        <option value="October">October</option>
        <option value="November">November</option>
        <option value="December">December</option>
      </select>
      <input
        id="year"
        type="number"
        class="form-control text-center"
        v-model="year"
        :placeholder="currentYear"
      />
      <select class="form-control text-center" v-model="level">
        <option value="PPO">PPO</option>
        <option value="RMFB">RMFB</option>
        <option value="Occidental">Occidental Mindoro</option>
        <option value="Oriental">Oriental Mindoro</option>
        <option value="Marinduque">Marinduque</option>
        <option value="Romblon">Romblon</option>
        <option value="Palawan">Palawan</option>
        <option value="Puerto">Puerto Princesa</option>
      </select>
      <button @click="fetchData" class="btn btn-success">Find</button>
    </div>
    <div v-if="error">{{ error }}</div>
    <div v-if="Object.keys(totalsByOffice).length === 0 && !error">
      No data found for the selected criteria.
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
      totalsByOffice: [], // Initialize totalsByOffice as an empty array
      error: null, // Initialize error to null
      month: "January",
      level: "PPO",
      chart: null, // Initialize chart to null
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
        .get(`/getRatePerMonth/${this.month}/${this.year}/${this.level}`)
        .then((response) => {
          const { totalsByOffice } = response.data;
          if (totalsByOffice && totalsByOffice.length > 0) {
            // Only update totalsByOffice if it's not null or undefined
            this.totalsByOffice = totalsByOffice;
          } else {
            console.error("Error: totalsByOffice is null or undefined");
            this.error = "No data found for the selected criteria.";
            setTimeout(() => {
              this.error = null;
            }, 5000);
          }
          this.renderChart(); // Always call renderChart whether data is found or not
        })
        .catch((error) => {
          console.error("Error fetching data:", error);
          this.error = "An error occurred while fetching data.";
        });
    },
    renderChart() {
      const ctx = this.$refs.chart.getContext("2d");
      if (this.chart) {
        // Destroy the existing chart if it exists
        this.chart.destroy();
      }
      const labels =
        this.totalsByOffice.length > 0
          ? this.totalsByOffice.map((office) => office.offices)
          : [];
      const data =
        this.totalsByOffice.length > 0
          ? this.totalsByOffice.map((office) => office.total)
          : [];
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
                "rgba(75, 192, 192, 0.2)", // Green
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
              text: `Monthly Rankings`,
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
  },
};
</script>

<style scoped>
#year {
  color: var(--dark);
  border: 1px solid var(--dark);
}
.Occidental {
  padding: 2rem;
  border-radius: 3rem;
}
</style>
