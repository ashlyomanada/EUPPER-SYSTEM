<template>
  <div class="head-title"></div>

  <div class="table-data">
    <div class="order d-flex flex-column gap-3">
      <div class="userDashboard">
        <div class="userBox shadow">
          <div class="statusContainer">
            <div class="statusBox">
              <h5>Welcome back</h5>
              <h5>Officer {{ officeLocation }}</h5>
            </div>

            <div class="statusBox">
              <h5><i class="fa-regular fa-clock"></i> Current Due Date</h5>
              <h5>2024-09-22 11:41:00</h5>
            </div>

            <div class="statusBox">
              <h5>
                <i
                  class="fa-solid fa-power-off"
                  :style="{ color: userStatus === 'Enable' ? 'green' : 'red' }"
                ></i
                >Rating Form Status
              </h5>
              <h5 :style="{ color: userStatus === 'Enable' ? 'green' : 'red' }">
                {{ userStatus }}
              </h5>
            </div>
          </div>
        </div>

        <!-- <RankingChart /> -->
        <div class="rankingChartUserContainer">
          <AllRankingChart />
        </div>
      </div>

      <div class="userDashboard">
        <div class="userBox shadow">
          <div class="d-flex justify-content-between align-items-center">
            <h5>
              <i class="fa-solid fa-calendar-days"></i>
              Your Ratings per Office by Month
            </h5>
            <h5>Current Maximum Rate: {{ maxRate }}</h5>
          </div>
          <UserChart />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import UserChart from "../components/Charts/UserChart.vue";
import AllRankingChart from "../components/Charts/AllRankingChart.vue";

export default {
  data() {
    return {
      month: "",
      year: 0,
      level: "ppo_cpo",
      rateRanking: [],
      officeLocation: "",
      userStatus: "",
      maxRate: null,
      selectedText: "PPO",
    };
  },

  components: {
    UserChart,
    AllRankingChart,
  },

  async mounted() {
    this.setMonthAndYear();
    await this.getRatePerRanking();
    await this.loadData();
  },

  methods: {
    async loadData() {
      try {
        const storedUserId = sessionStorage.getItem("id");
        if (storedUserId) {
          const response = await axios.get(`/getUserData/${storedUserId}`);
          const userData = response.data;
          this.officeLocation = userData.username;
          this.userStatus = userData.status;
          this.maxRate = userData.maxRate;
          // console.log(userData);
        }
      } catch (error) {
        console.error("Error fetching user data:", error);
      }
    },

    setMonthAndYear() {
      const currentDate = new Date();
      let currentMonth = currentDate.getMonth(); // 0 = January, 11 = December
      let currentYear = currentDate.getFullYear();

      // If it's January (month 0), set the month to December and subtract one year
      if (currentMonth === 0) {
        this.month = "December";
        this.year = currentYear - 1;
      } else {
        // Set the month to the previous month
        const months = [
          "January",
          "February",
          "March",
          "April",
          "May",
          "June",
          "July",
          "August",
          "September",
          "October",
          "November",
          "December",
        ];
        this.month = months[currentMonth - 1];
        this.year = currentYear;
      }
    },

    async getRatePerRanking() {
      try {
        const selectElement = document.querySelector("#selectedTable3");
        const selectedOption =
          selectElement.options[selectElement.selectedIndex];
        this.selectedText = selectedOption.text; // Use the innerText of the selected option

        const response = await axios.get(
          `/getRatePerRanking/${this.month}/${this.year}/${this.level}`
        );
        if (response.data.totalsByOffice) {
          this.rateRanking = response.data.totalsByOffice;
        }
      } catch (e) {
        console.log(e);
      }
    },
  },
};
</script>
<style>
.userBox {
  display: flex;
  flex-direction: column;
  padding: 2rem;
  gap: 1rem;
  border-radius: 2rem;
}

.shadow {
  box-shadow: rgba(0, 0, 0, 0.4) 0px 2px 4px,
    rgba(0, 0, 0, 0.3) 0px 7px 13px -3px, rgba(0, 0, 0, 0.2) 0px -3px 0px inset;
}
.monthlyDashboard {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  grid-template-rows: auto 1fr;
}
.userDashboard {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  grid-template-rows: auto 1fr;
  -moz-column-gap: 1rem;
  column-gap: 1rem;
  padding: 1rem;
  color: var(--dark);
  background: var(--light);
}

.statusBox {
  display: flex;
  height: 100%;
  width: 100%;
  border-radius: 1rem;
  padding: 2rem;
  gap: 2rem;
  align-items: center;
  box-shadow: rgba(0, 0, 0, 0.4) 0px 2px 4px,
    rgba(0, 0, 0, 0.3) 0px 7px 13px -3px, rgba(0, 0, 0, 0.2) 0px -3px 0px inset;
}

.statusContainer {
  display: grid;
  grid-template-columns: repeat(1, 1fr);
  grid-template-rows: repeat(3, 1fr);
  place-items: center;
  height: 100%;
  gap: 1rem;
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
  .rankingChartUserContainer {
    margin-left: 2rem;
  }
}
</style>
