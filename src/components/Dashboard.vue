<template>
  <div class="head-title"></div>

  <div class="dash-box">
    <div class="monthlyDashboard">
      <!-- <ul class="box-info">
        <li>
          <i class="bx bxs-calendar-check"></i>
          <span class="text">
            <h3></h3>
            <p>Ratings</p>
          </span>
        </li>
        <li>
          <i class="bx bxs-group"></i>
          <span class="text">
            <h3></h3>
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
      </ul> -->
      <MonthChart />
    </div>
    <div class="mainDashboard">
      <PPOBarChart />
      <RMFBBarChart />
      <OccidentalBarChart />
      <OrientalBarChart />
      <MarinduqueBarChart />
      <RomblonBarChart />
      <PalawanBarChart />
      <PuertoBarChart />
    </div>
  </div>
</template>

<script>
import { defineComponent, PropType } from "vue";
import MonthChart from "./Charts/MonthChart.vue";
import PPOBarChart from "./Charts/PPOBarChart.vue";
import RMFBBarChart from "./Charts/RMFBBarChart.vue";
import OccidentalBarChart from "./Charts/OccidentalBarChart.vue";
import OrientalBarChart from "./Charts/OrientalBarChart.vue";
import MarinduqueBarChart from "./Charts/MarinduqueBarChart.vue";
import RomblonBarChart from "./Charts/RomblonBarChart.vue";
import PalawanBarChart from "./Charts/PalawanBarChart.vue";
import PuertoBarChart from "./Charts/PuertoBarChart.vue";

export default defineComponent({
  data() {
    return {
      ratingCount: 0,
      userCount: 0,
    };
  },

  components: {
    MonthChart,
    PPOBarChart,
    RMFBBarChart,
    OccidentalBarChart,
    OrientalBarChart,
    MarinduqueBarChart,
    RomblonBarChart,
    PalawanBarChart,
    PuertoBarChart,
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
.monthlyDashboard {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  grid-template-rows: auto 1fr;
}
.mainDashboard {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  grid-template-rows: auto 1fr;
  gap: 1rem;
  padding-top: 1rem;
  padding-bottom: 1rem;
}
.dash-box {
  display: grid;
  grid-template-columns: repeat(1, 1fr);
  grid-template-rows: repeat(1, 1fr);
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

@media screen and (max-width: 600px) {
  .mainDashboard {
    grid-template-columns: repeat(1, 300px);
    grid-template-rows: repeat(8, 300px);
    gap: 2rem;
    justify-content: center;
  }
}
</style>
