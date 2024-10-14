<template>
  <div
    class="d-flex gap-2 justify-content-center flex-column align-items-center"
  >
    <canvas id="locationRateBarChart"></canvas>

    <div class="d-flex gap-2">
      <select v-model="selectedTable" class="form-select" name="month" required>
        <option value="ppo_cpo">PPO CPO LEVEL</option>
        <option value="rmfb_tbl">RMFB PMFC LEVEL</option>
        <option value="occidental_cps">Occidental Mindoro MPS</option>
        <option value="oriental_cps">Oriental Mindoro MPS</option>
        <option value="marinduque_cps">Marinduque MPS</option>
        <option value="romblon_cps">Romblon MPS</option>
        <option value="palawan_cps">Palawan MPS</option>
        <option value="puertop_cps">Puerto Princesa MPS</option>
      </select>
      <input type="number" v-model="currentYear" class="form-control" />
      <button
        :disabled="isButtonDisabled"
        class="btn btn-success d-flex gap-2 align-items-center justify-content-center"
        @click.prevent="onTableChange"
      >
        <i class="fa-solid fa-magnifying-glass"></i>Find
      </button>
    </div>

    <div v-if="errorMessage" class="alert alert-danger">
      {{ errorMessage }}
    </div>
  </div>
</template>

<script>
import {
  Chart,
  BarController,
  BarElement,
  CategoryScale, // Register this for the X-axis
  LinearScale,
  Title,
  Tooltip,
  Legend,
} from "chart.js";
import axios from "axios";

// Register the required components for Chart.js
Chart.register(
  BarController,
  BarElement,
  CategoryScale,
  LinearScale,
  Title,
  Tooltip,
  Legend
);

export default {
  data() {
    return {
      rateData: [], // Holds user rating data from the backend
      columns: [], // Holds dynamically fetched office names
      selectedTable: "ppo_cpo", // Default selected table
      currentYear: new Date().getFullYear(), // Default to current year
      chartInstance: null, // Chart.js instance
      errorMessage: null, // Error message if no data is found
      isButtonDisabled: false,
    };
  },
  async mounted() {
    await this.fetchColumns(); // Fetch office names dynamically
    await this.fetchRateData(); // Fetch user rate data from the backend
  },
  methods: {
    async onTableChange() {
      await this.fetchColumns(); // Fetch new columns when table changes
      await this.fetchRateData(); // Fetch new data for the selected table
      this.isButtonDisabled = true;
      setTimeout(() => {
        this.isButtonDisabled = false;
      }, 1000); // Simulating delay, use actual logic
    },
    async fetchColumns() {
      try {
        const response = await axios.get(
          `/getColumnNameFromTable/${this.selectedTable}`
        );
        this.columns = response.data.filter(
          (column) => !["id", "userid", "month", "year"].includes(column)
        );
      } catch (error) {
        console.error("Error fetching column names:", error);
      }
    },

    async fetchRateData() {
      const userId = sessionStorage.getItem("id");
      const table = this.selectedTable;
      const year = this.currentYear;

      try {
        const response = await axios.get(
          `/viewUserAnalytics/${userId}/${table}/${year}`
        );

        if (response.data && response.data.length) {
          this.rateData = response.data;
          this.errorMessage = null;

          await this.$nextTick(); // Ensure the DOM is updated before updating the chart
          this.updateBarChart();
        } else {
          this.rateData = [];
          this.errorMessage = "No data found for the selected table and year.";
          this.clearChart();
        }
      } catch (error) {
        console.error("Error fetching rate data:", error);
        this.errorMessage = "An error occurred while fetching the data.";
        this.clearChart();
      }
    },

    clearChart() {
      if (this.chartInstance) {
        this.chartInstance.destroy(); // Destroy the existing chart
        this.chartInstance = null; // Reset the chart instance
      }
    },

    updateBarChart() {
      // Ensure the canvas element exists
      const canvas = document.getElementById("locationRateBarChart");
      if (!canvas) {
        console.error("Canvas element not found");
        return;
      }

      const context = canvas.getContext("2d");
      if (!context) {
        console.error("Canvas context not available");
        return;
      }

      // Return early if no data
      if (!this.rateData.length) {
        this.clearChart();
        return;
      }

      const months = this.rateData.map((data) => data.month);

      const datasets = this.columns.map((office) => ({
        label: this.formatOfficeName(office),
        data: this.rateData.map((data) => data[office] || 0),
        backgroundColor: this.getRandomColor(),
        borderWidth: 1,
      }));

      if (this.chartInstance) {
        this.clearChart(); // Destroy the existing chart before creating a new one
      }

      this.chartInstance = new Chart(context, {
        type: "bar",
        data: {
          labels: months,
          datasets: datasets,
        },
        options: {
          responsive: true,
          scales: {
            x: {
              type: "category",
              stacked: false,
            },
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: "Rates",
              },
            },
          },
          plugins: {
            title: {
              display: true,
              text: "",
            },
          },
        },
      });
    },
    formatOfficeName(office) {
      return office.replace(/_/g, " ");
    },

    getRandomColor() {
      const r = Math.floor(Math.random() * 255);
      const g = Math.floor(Math.random() * 255);
      const b = Math.floor(Math.random() * 255);
      return `rgba(${r}, ${g}, ${b}, 0.6)`;
    },
  },
};
</script>
