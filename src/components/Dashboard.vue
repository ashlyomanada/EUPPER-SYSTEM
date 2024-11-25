<template>
  <div class="head-title"></div>

  <div class="dash-box">
    <div class="adminDashboardContainer">
      <div class="adminStatusBox">
        <div class="d-flex align-items-center justify-content-center gap-2">
          <i class="fa-solid fa-users" style="font-size: 3rem"></i>
          <h4>No. of Users</h4>
        </div>
        <h4>{{ userCount }}</h4>
      </div>

      <div class="adminStatusBox">
        <div class="d-flex align-items-center justify-content-center gap-2">
          <i class="fa-regular fa-clock" style="font-size: 3rem"></i>
          <h4>Due Date</h4>
        </div>

        <h4>{{ currentDue }}</h4>
      </div>
    </div>

    <div class="adminDashboardContainer">
      <MonthChart />
      <AverageChart />
    </div>

    <div class="adminDashboardContainer">
      <AllRankingChart />
      <PredictionChart />
    </div>

    <div class="adminDashboardContainer">
      <RateChart />
    </div>
  </div>
</template>

<script>
import { defineComponent, PropType } from "vue";
import MonthChart from "./Charts/MonthChart.vue";
import AverageChart from "./Charts/AverageChart.vue";
import axios from "axios";
import AllRankingChart from "../components/Charts/AllRankingChart.vue";
import RateChart from "../components/Charts/RateChart.vue";
import PredictionChart from "../components/Charts/PredictionChart.vue";

export default defineComponent({
  data() {
    return {
      ratingCount: 0,
      userCount: 0,
      baseURL: axios.defaults.baseURL,
      currentDue: null,
    };
  },

  components: {
    MonthChart,
    AverageChart,
    AllRankingChart,
    RateChart,
    PredictionChart,
  },
  async mounted() {
    await this.fetchUserCount();
    await this.getDueDate();
  },
  methods: {
    async fetchUserCount() {
      try {
        const response = await fetch(`${this.baseURL}/countUser`);
        const data = await response.json();
        this.userCount = data.count;
        //console.log(data);
      } catch (error) {
        console.error("Error fetching rating count:", error);
      }
    },
    async getDueDate() {
      try {
        const response = await axios.get("/selectDue");
        this.currentDue = response.data.date;
      } catch (e) {
        console.log(e);
      }
    },
  },
});
</script>
<style>
.monthChartContainer {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  grid-template-rows: auto 1fr;
  gap: 1rem;
}
.adminDashboardContainer {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  grid-template-rows: auto 1fr;
  column-gap: 1rem;
  padding: 1rem;
  color: var(--dark);
  background: var(--light);
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

.adminBox {
  height: 100%;
  width: 100%;
}

.adminStatusBox {
  display: flex;
  justify-content: space-between;
  height: 150px;
  width: 100%;
  border-radius: 1rem;
  padding: 2rem;
  align-items: center;
  box-shadow: rgba(0, 0, 0, 0.3) 0px 2px 4px,
    rgba(0, 0, 0, 0.2) 0px 7px 13px -3px, rgba(0, 0, 0, 0.1) 0px -3px 0px inset;
}

@media screen and (max-width: 600px) {
  .mainDashboard {
    grid-template-columns: repeat(1, 300px);
    grid-template-rows: repeat(8, 300px);
    gap: 2rem;
    justify-content: center;
  }

  .adminDashboardContainer {
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
  }
}
</style>
