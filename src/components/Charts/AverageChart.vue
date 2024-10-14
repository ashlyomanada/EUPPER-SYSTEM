<template>
  <div class="PPOCard shadow">
    <canvas ref="chart" width="400" height="200"></canvas>
    <div class="d-flex justify-content-center gap-2">
      <select
        id="selectedTable"
        class="form-control text-center"
        v-model="selectedLevel"
      >
        <option class="levelValue" value="ppo_cpo">PPO</option>
        <option class="levelValue" value="rmfb_tbl">RMFB</option>
        <option class="levelValue" value="occidental_cps">
          Occidental Mindoro
        </option>
        <option class="levelValue" value="oriental_cps">
          Oriental Mindoro
        </option>
        <option class="levelValue" value="marinduque_cps">Marinduque</option>
        <option class="levelValue" value="romblon_cps">Romblon</option>
        <option class="levelValue" value="palawan_cps">Palawan</option>
        <option class="levelValue" value="puertop_cps">Puerto Princesa</option>
      </select>
      <input
        id="averageYear"
        type="number"
        class="form-control text-center"
        v-model="year"
        :placeholder="currentYear"
        min="2000"
        max="2100"
      />
      <button @click="fetchData" class="btn btn-success">Find</button>
    </div>
    <div v-if="Object.keys(totalsByOffice).length === 0 && !error">
      No data found for the selected year.
    </div>
    <div v-if="error">{{ error }}</div>
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
      error: "", // Initialize error to null
      selectedLevel: "ppo_cpo",
      selectedText: "", // Initially empty
      chart: null,
    };
  },

  computed: {
    currentYear() {
      return new Date().getFullYear();
    },
  },

  mounted() {
    this.fetchData(); // Fetch data on load (if needed, can be removed)
  },

  methods: {
    fetchData() {
      this.error = null; // Reset error message

      // Update the selectedText only when the Find button is clicked
      const selectElement = document.querySelector("#selectedTable");
      const selectedOption = selectElement.options[selectElement.selectedIndex];
      this.selectedText = selectedOption.text; // Use the innerText of the selected option

      axios
        .get(`/getAllAverageRatesPerTbl/${this.selectedLevel}/${this.year}`)
        .then((response) => {
          const { totalsByOffice } = response.data;
          if (totalsByOffice && Object.keys(totalsByOffice).length > 0) {
            this.totalsByOffice = totalsByOffice;
            const labels = this.formatLabels(Object.keys(totalsByOffice));
            this.renderChart(labels, Object.values(totalsByOffice));
          } else {
            console.error("Error: totalsByOffice is null or empty");
            this.totalsByOffice = {}; // Clear the data
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
              text: `${this.selectedText} Offices Average per Year`,
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
.PPOCard {
  padding: 2rem;
  border-radius: 2rem;
}

@media screen and (max-width: 600px) {
  #year {
    width: 30%;
  }
}
</style>
