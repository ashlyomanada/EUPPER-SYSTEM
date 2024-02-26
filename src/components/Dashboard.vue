<template>
  <div class="head-title">
    <div class="left">
      <h1 id="dashboardTitle">Dashboard</h1>
    </div>
  </div>

  <div class="dash-box">
    <div>
      <pie-chart
        :chart-data="pieChartData"
        :chart-options="pieChartOptions"
      ></pie-chart>
      <form @submit.prevent="fetchData" class="year-container">
        <span>Select Year:</span>
        <input
          type="number"
          id="selectedYear"
          v-model="selectedYear"
          min="2000"
          max="2100"
          required
        />
        <button type="submit" class="findRate">Get Ratings</button>
      </form>
    </div>
    <ul class="box-info">
      <li>
        <i class="bx bxs-calendar-check"></i>
        <span class="text">
          <h3>{{ ratingCount }}</h3>
          <p>Ratings</p>
        </span>
      </li>
      <li>
        <i class="bx bxs-group"></i>
        <span class="text">
          <h3>{{ userCount }}</h3>
          <p>Users</p>
        </span>
      </li>
      <li>
        <i class="bx bxs-calendar-check"></i>
        <span class="text">
          <h3>0</h3>
          <p>Announcements</p>
        </span>
      </li>
    </ul>
  </div>
</template>

<script>
import PieChart from "./PieChart.vue";

export default {
  data() {
    return {
      ratingCount: 0,
      userCount: 0,
      selectedYear: new Date().getFullYear(),
      pieChartData: {
        labels: [],
        datasets: [
          {
            data: [],
            backgroundColor: [],
            hoverBackgroundColor: [],
          },
        ],
      },
      pieChartOptions: {
        responsive: true,
        maintainAspectRatio: false,
      },
    };
  },
  components: {
    PieChart,
  },
  mounted() {
    this.fetchRatingCount();
    this.fetchUserCount();
    this.fetchData();
  },
  methods: {
    async fetchRatingCount() {
      try {
        const response = await fetch(
          "https://e-upper.online/backend/countUserRatings"
        );
        const data = await response.json();
        this.ratingCount = data.count;
        //console.log(data);
      } catch (error) {
        console.error("Error fetching rating count:", error);
      }
    },
    async fetchUserCount() {
      try {
        const response = await fetch(
          "https://e-upper.online/backend/countUser"
        );
        const data = await response.json();
        this.userCount = data.count;
        //console.log(data);
      } catch (error) {
        console.error("Error fetching rating count:", error);
      }
    },
    async fetchData() {
      try {
        const response = await fetch(
          `https://e-upper.online/backend/calculateRatings/${this.selectedYear}`
        );
        const data = await response.json();

        // Update your chart data and other components based on the response...
        this.pieChartData.labels = data.months;
        this.pieChartData.datasets[0].data = data.formattedPercentages;
        this.pieChartData.datasets[0].backgroundColor = data.backgroundColor;
        this.pieChartData.datasets[0].hoverBackgroundColor =
          data.hoverBackgroundColor;
      } catch (error) {
        console.error("Error fetching data:", error);
      }
    },
  },
};
</script>
<style>
.dash-box {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  grid-template-rows: 0.5fr;
}
.findRate {
  background: green;
  padding: 0.2rem 0.5rem;
  color: white;
}
.year-container {
  display: flex;
  align-items: center;
  gap: 2rem;
  margin-top: 1rem;
}
</style>
