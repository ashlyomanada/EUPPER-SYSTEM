<template>
  <div class="head-title"></div>

  <div class="dash-box">
    <BarChart />

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
import { defineComponent, PropType } from "vue";
import BarChart from "../components/BarChart.vue";

export default defineComponent({
  data() {
    return {
      ratingCount: 0,
      userCount: 0,
    };
  },

  components: {
    BarChart,
  },
  mounted() {
    this.fetchRatingCount();
    this.fetchUserCount();
    //this.fetchData();
  },
  methods: {
    async fetchRatingCount() {
      try {
        const response = await fetch("http://localhost:8080/countUserRatings");
        const data = await response.json();
        this.ratingCount = data.count;
        //console.log(data);
      } catch (error) {
        console.error("Error fetching rating count:", error);
      }
    },
    async fetchUserCount() {
      try {
        const response = await fetch("http://localhost:8080/countUser");
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
          `http://localhost:8080/calculateRatings/${this.selectedYear}`
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
});
</script>
<style>
.dash-box {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  grid-template-rows: repeat(2, 1fr);
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
