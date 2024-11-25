<template>
  <div class="monthlyContainer shadow">
    <canvas ref="chart" width="400" height="200"></canvas>
    <div class="monthlyControls">
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
        id="monthlyYear"
        type="number"
        class="form-control text-center"
        v-model="year"
        :placeholder="currentYear"
      />

      <select
        id="selectedTable2"
        class="form-control text-center"
        v-model="level"
      >
        <option value="ppo_cpo">PPO</option>
        <option value="rmfb_tbl">RMFB</option>
        <option value="occidental_cps">Occidental Mindoro</option>
        <option value="oriental_cps">Oriental Mindoro</option>
        <option value="marinduque_cps">Marinduque</option>
        <option value="romblon_cps">Romblon</option>
        <option value="palawan_cps">Palawan</option>
        <option value="puertop_cps">Puerto Princesa</option>
      </select>
      <button @click="fetchData" class="btn btn-success" :disabled="isLoading">
        <span v-if="!isLoading" class="d-flex gap-2 align-items-center">
          <i v-if="!isLoading" class="fa-solid fa-magnifying-glass"></i>Find
        </span>
        <span v-if="isLoading" class="d-flex gap-2 align-items-center">
          <i class="fa-solid fa-spinner"></i>Finding
        </span>
      </button>
    </div>
    <div class="text-center my-2" v-if="error">{{ error }}</div>
    <div
      class="text-center my-2"
      v-if="Object.keys(totalsByOffice).length === 0 && !error"
    >
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
      month: "",
      level: "ppo_cpo",
      chart: null, // Initialize chart to null
      selectedText: "PPO", //
      isLoading: false,
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

  created() {
    const currentDate = new Date();
    currentDate.setMonth(currentDate.getMonth() - 1); // Subtract one month
    this.month = currentDate.toLocaleString("default", { month: "long" });
  },
  methods: {
    fetchData() {
      const selectElement = document.querySelector("#selectedTable2");
      const selectedOption = selectElement.options[selectElement.selectedIndex];
      this.selectedText = selectedOption.text; // Use the innerText of the selected option

      this.isLoading = true;

      setTimeout(() => {
        this.isLoading = false;
      }, 1000);
      axios
        .get(`/getRatePerMonth/${this.month}/${this.year}/${this.level}`)
        .then((response) => {
          const { totalsByOffice } = response.data;
          if (totalsByOffice && totalsByOffice.length > 0) {
            // Only update totalsByOffice if it's not null or undefined
            this.totalsByOffice = totalsByOffice;
            this.error = null;
          } else {
            // console.error("Error: totalsByOffice is null or undefined");
            this.error = "No data found for the selected criteria.";
            this.totalsByOffice = [];
            setTimeout(() => {
              this.error = null;
            }, 5000);
          }
          this.renderChart(); // Always call renderChart whether data is found or not
        })
        .catch((error) => {
          console.error("Error fetching data:", error);
          this.error = "No data found for the selected criteria.";
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
              text: `${this.selectedText} Monthly Rankings`,
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

<style>
.monthlyContainer {
  display: flex;
  /* max-width: 90vw;
  max-height: 90vh; */
  justify-content: center;
  flex-direction: column;
  border-radius: 2rem;
  padding: 2rem;
}
.Occidental {
  padding: 2rem;
  border-radius: 3rem;
}
.monthlyControls {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
}
.monthlyYear {
  color: var(--dark);
  border: 1px solid var(--dark);
}
</style>
