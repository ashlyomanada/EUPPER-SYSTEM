<template>
  <div class="barChartContainer">
    <div class="chart-container">
      <label class="chartName" for="">PPO Ratings</label>
      <canvas ref="groupedBarChartPPO"></canvas>
    </div>

    <div class="chart-container">
      <label class="chartName" for="">RMFB Ratings</label>
      <canvas ref="groupedBarChartRMFB"></canvas>
    </div>

    <div class="chart-container">
      <label class="chartName" for="">Occidental Mindoro Ratings</label>
      <canvas ref="groupedBarChartOccidental"></canvas>
    </div>

    <div class="chart-container">
      <label class="chartName" for="">Oriental Mindoro Ratings</label>
      <canvas ref="groupedBarChartOriental"></canvas>
    </div>

    <div class="chart-container">
      <label class="chartName" for="">Marinduque Ratings</label>
      <canvas ref="groupedBarChartMarinduque"></canvas>
    </div>

    <div class="chart-container">
      <label class="chartName" for="">Romblon Ratings</label>
      <canvas ref="groupedBarChartRomblon"></canvas>
    </div>

    <div class="chart-container">
      <label class="chartName" for="">Palawan Ratings</label>
      <canvas ref="groupedBarChartPalawan"></canvas>
    </div>

    <div class="chart-container">
      <label class="chartName" for="">Puerto Ratings</label>
      <canvas ref="groupedBarChartPuerto"></canvas>
    </div>
  </div>
</template>

<script>
import Chart from "chart.js/auto";
import axios from "axios";

export default {
  data() {
    return {
      chartDataPPO: { labels: [], datasets: [] },
      chartDataRMFB: { labels: [], datasets: [] },
      chartDataOccidental: { labels: [], datasets: [] },
      chartDataOriental: { labels: [], datasets: [] },
      chartDataMarinduque: { labels: [], datasets: [] },
      chartDataRomblon: { labels: [], datasets: [] },
      chartDataPalawan: { labels: [], datasets: [] },
      chartDataPuerto: { labels: [], datasets: [] },
    };
  },
  mounted() {
    this.getAllRatesPPO();
    this.getAllRatesRMFB();
    this.getAllRatesOccidental();
    this.getAllRatesOriental();
    this.getAllRatesMarinduque();
    this.getAllRatesRomblon();
    this.getAllRatesPalawan();
    this.getAllRatesPuerto();
  },
  methods: {
    async getAllRatesPPO() {
      try {
        const response = await axios.get("/getAllRatesPPO");
        const rates = response.data;

        // Prepare data for PPO Rates chart
        const offices = {};
        rates.forEach((rate) => {
          const officeName = rate.offices;
          const month = rate.month;
          const total = parseFloat(rate.total);

          if (!offices[officeName]) {
            offices[officeName] = {
              label: officeName,
              data: [],
              backgroundColor: this.getRandomColor(),
            };
          }

          const index = this.chartDataPPO.labels.indexOf(month);
          if (index === -1) {
            this.chartDataPPO.labels.push(month);
          }
          offices[officeName].data[index] = total;
        });

        Object.values(offices).forEach((office) => {
          this.chartDataPPO.datasets.push(office);
        });

        this.renderGroupedBarChartPPO();
      } catch (error) {
        console.error("Error fetching PPO rates:", error);
      }
    },

    async getAllRatesRMFB() {
      try {
        const response = await axios.get("/getAllRatesRMFB");
        const rates = response.data;

        // Prepare data for RMFB Rates chart
        const offices = {};
        rates.forEach((rate) => {
          const officeName = rate.offices;
          const month = rate.month;
          const total = parseFloat(rate.total);

          if (!offices[officeName]) {
            offices[officeName] = {
              label: officeName,
              data: [],
              backgroundColor: this.getRandomColor(),
            };
          }

          const index = this.chartDataRMFB.labels.indexOf(month);
          if (index === -1) {
            this.chartDataRMFB.labels.push(month);
          }
          offices[officeName].data[index] = total;
        });

        Object.values(offices).forEach((office) => {
          this.chartDataRMFB.datasets.push(office);
        });

        this.renderGroupedBarChartRMFB();
      } catch (error) {
        console.error("Error fetching RMFB rates:", error);
      }
    },

    async getAllRatesOccidental() {
      try {
        const response = await axios.get("/getAllRatesOccidental");
        const rates = response.data;

        // Prepare data for RMFB Rates chart
        const offices = {};
        rates.forEach((rate) => {
          const officeName = rate.offices;
          const month = rate.month;
          const total = parseFloat(rate.total);

          if (!offices[officeName]) {
            offices[officeName] = {
              label: officeName,
              data: [],
              backgroundColor: this.getRandomColor(),
            };
          }

          const index = this.chartDataOccidental.labels.indexOf(month);
          if (index === -1) {
            this.chartDataOccidental.labels.push(month);
          }
          offices[officeName].data[index] = total;
        });

        Object.values(offices).forEach((office) => {
          this.chartDataOccidental.datasets.push(office);
        });

        this.renderGroupedBarChartOccidental();
      } catch (error) {
        console.error("Error fetching RMFB rates:", error);
      }
    },

    async getAllRatesOriental() {
      try {
        const response = await axios.get("/getAllRatesOriental");
        const rates = response.data;

        // Prepare data for RMFB Rates chart
        const offices = {};
        rates.forEach((rate) => {
          const officeName = rate.offices;
          const month = rate.month;
          const total = parseFloat(rate.total);

          if (!offices[officeName]) {
            offices[officeName] = {
              label: officeName,
              data: [],
              backgroundColor: this.getRandomColor(),
            };
          }

          const index = this.chartDataOriental.labels.indexOf(month);
          if (index === -1) {
            this.chartDataOriental.labels.push(month);
          }
          offices[officeName].data[index] = total;
        });

        Object.values(offices).forEach((office) => {
          this.chartDataOriental.datasets.push(office);
        });

        this.renderGroupedBarChartOriental();
      } catch (error) {
        console.error("Error fetching RMFB rates:", error);
      }
    },

    async getAllRatesMarinduque() {
      try {
        const response = await axios.get("/getAllRatesMarinduque");
        const rates = response.data;

        // Prepare data for RMFB Rates chart
        const offices = {};
        rates.forEach((rate) => {
          const officeName = rate.offices;
          const month = rate.month;
          const total = parseFloat(rate.total);

          if (!offices[officeName]) {
            offices[officeName] = {
              label: officeName,
              data: [],
              backgroundColor: this.getRandomColor(),
            };
          }

          const index = this.chartDataMarinduque.labels.indexOf(month);
          if (index === -1) {
            this.chartDataMarinduque.labels.push(month);
          }
          offices[officeName].data[index] = total;
        });

        Object.values(offices).forEach((office) => {
          this.chartDataMarinduque.datasets.push(office);
        });

        this.renderGroupedBarChartMarinduque();
      } catch (error) {
        console.error("Error fetching RMFB rates:", error);
      }
    },

    async getAllRatesRomblon() {
      try {
        const response = await axios.get("/getAllRatesRomblon");
        const rates = response.data;

        // Prepare data for RMFB Rates chart
        const offices = {};
        rates.forEach((rate) => {
          const officeName = rate.offices;
          const month = rate.month;
          const total = parseFloat(rate.total);

          if (!offices[officeName]) {
            offices[officeName] = {
              label: officeName,
              data: [],
              backgroundColor: this.getRandomColor(),
            };
          }

          const index = this.chartDataRomblon.labels.indexOf(month);
          if (index === -1) {
            this.chartDataRomblon.labels.push(month);
          }
          offices[officeName].data[index] = total;
        });

        Object.values(offices).forEach((office) => {
          this.chartDataRomblon.datasets.push(office);
        });

        this.renderGroupedBarChartRomblon();
      } catch (error) {
        console.error("Error fetching RMFB rates:", error);
      }
    },

    async getAllRatesPalawan() {
      try {
        const response = await axios.get("/getAllRatesPalawan");
        const rates = response.data;

        // Prepare data for RMFB Rates chart
        const offices = {};
        rates.forEach((rate) => {
          const officeName = rate.offices;
          const month = rate.month;
          const total = parseFloat(rate.total);

          if (!offices[officeName]) {
            offices[officeName] = {
              label: officeName,
              data: [],
              backgroundColor: this.getRandomColor(),
            };
          }

          const index = this.chartDataPalawan.labels.indexOf(month);
          if (index === -1) {
            this.chartDataPalawan.labels.push(month);
          }
          offices[officeName].data[index] = total;
        });

        Object.values(offices).forEach((office) => {
          this.chartDataPalawan.datasets.push(office);
        });

        this.renderGroupedBarChartPalawan();
      } catch (error) {
        console.error("Error fetching RMFB rates:", error);
      }
    },

    async getAllRatesPuerto() {
      try {
        const response = await axios.get("/getAllRatesPuerto");
        const rates = response.data;

        // Prepare data for RMFB Rates chart
        const offices = {};
        rates.forEach((rate) => {
          const officeName = rate.offices;
          const month = rate.month;
          const total = parseFloat(rate.total);

          if (!offices[officeName]) {
            offices[officeName] = {
              label: officeName,
              data: [],
              backgroundColor: this.getRandomColor(),
            };
          }

          const index = this.chartDataPuerto.labels.indexOf(month);
          if (index === -1) {
            this.chartDataPuerto.labels.push(month);
          }
          offices[officeName].data[index] = total;
        });

        Object.values(offices).forEach((office) => {
          this.chartDataPuerto.datasets.push(office);
        });

        this.renderGroupedBarChartPuerto();
      } catch (error) {
        console.error("Error fetching RMFB rates:", error);
      }
    },

    renderGroupedBarChartPPO() {
      const ctx = this.$refs.groupedBarChartPPO.getContext("2d");
      new Chart(ctx, {
        type: "bar",
        data: this.chartDataPPO,
        options: {
          indexAxis: "y",
          responsive: true,
          plugins: {
            legend: { position: "top" },
          },
          scales: {
            x: { stacked: true },
            y: { stacked: false, beginAtZero: true },
          },
        },
      });
    },

    renderGroupedBarChartRMFB() {
      const ctx = this.$refs.groupedBarChartRMFB.getContext("2d");
      new Chart(ctx, {
        type: "bar",
        data: this.chartDataRMFB,
        options: {
          indexAxis: "y",
          responsive: true,
          plugins: {
            legend: { position: "top" },
          },
          scales: {
            x: { stacked: true },
            y: { stacked: false, beginAtZero: true },
          },
        },
      });
    },

    renderGroupedBarChartOccidental() {
      const ctx = this.$refs.groupedBarChartOccidental.getContext("2d");
      new Chart(ctx, {
        type: "bar",
        data: this.chartDataOccidental,
        options: {
          indexAxis: "y",
          responsive: true,
          plugins: {
            legend: { position: "top" },
          },
          scales: {
            x: { stacked: true },
            y: { stacked: false, beginAtZero: true },
          },
        },
      });
    },

    renderGroupedBarChartOriental() {
      const ctx = this.$refs.groupedBarChartOriental.getContext("2d");
      new Chart(ctx, {
        type: "bar",
        data: this.chartDataOriental,
        options: {
          indexAxis: "y",
          responsive: true,
          plugins: {
            legend: { position: "top" },
          },
          scales: {
            x: { stacked: true },
            y: { stacked: false, beginAtZero: true },
          },
        },
      });
    },

    renderGroupedBarChartMarinduque() {
      const ctx = this.$refs.groupedBarChartMarinduque.getContext("2d");
      new Chart(ctx, {
        type: "bar",
        data: this.chartDataMarinduque,
        options: {
          indexAxis: "y",
          responsive: true,
          plugins: {
            legend: { position: "top" },
          },
          scales: {
            x: { stacked: true },
            y: { stacked: false, beginAtZero: true },
          },
        },
      });
    },

    renderGroupedBarChartRomblon() {
      const ctx = this.$refs.groupedBarChartRomblon.getContext("2d");
      new Chart(ctx, {
        type: "bar",
        data: this.chartDataRomblon,
        options: {
          indexAxis: "y",
          responsive: true,
          plugins: {
            legend: { position: "top" },
          },
          scales: {
            x: { stacked: true },
            y: { stacked: false, beginAtZero: true },
          },
        },
      });
    },

    renderGroupedBarChartPalawan() {
      const ctx = this.$refs.groupedBarChartPalawan.getContext("2d");
      new Chart(ctx, {
        type: "bar",
        data: this.chartDataPalawan,
        options: {
          indexAxis: "y",
          responsive: true,
          plugins: {
            legend: { position: "top" },
          },
          scales: {
            x: { stacked: true },
            y: { stacked: false, beginAtZero: true },
          },
        },
      });
    },

    renderGroupedBarChartPuerto() {
      const ctx = this.$refs.groupedBarChartPuerto.getContext("2d");
      new Chart(ctx, {
        type: "bar",
        data: this.chartDataPuerto,
        options: {
          indexAxis: "y",
          responsive: true,
          plugins: {
            legend: { position: "top" },
          },
          scales: {
            x: { stacked: true },
            y: { stacked: false, beginAtZero: true },
          },
        },
      });
    },

    getRandomColor() {
      return "#" + Math.floor(Math.random() * 16777215).toString(16);
    },
  },
};
</script>

<style scoped>
.chart-container {
  padding-top: 2rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  background: var(--light);
  border-radius: 3rem;
  padding: 1rem;
}

canvas {
  height: 35vh;
  margin: 0 auto;
  color: var(--dark);
}

.chartName {
  color: var(--dark);
}

.barChartContainer {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  grid-template-rows: repeat(4, 1fr);
  gap: 1.5rem;
}
</style>
