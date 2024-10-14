<template>
  <div class="adminBox shadow">
    <h5>{{ selectedText }} Offices Ranks for the last {{ currentMonth }}</h5>

    <table>
      <thead>
        <tr>
          <th><i class="fa-solid fa-ranking-star"></i>Rank</th>
          <th>Offices</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(rateRankings, index) in rateRanking" :key="index">
          <td>{{ index + 1 }}</td>
          <td>{{ rateRankings.offices }}</td>
          <td>{{ rateRankings.total }}</td>
        </tr>
      </tbody>
    </table>

    <h6 class="text-center" v-if="rateRanking.length === 0">No data found</h6>

    <div class="d-flex justify-content-center gap-2 align-items-center">
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
      />

      <select
        id="selectedTable3"
        v-model="level"
        class="form-select"
        name="month"
        required
      >
        <option value="ppo_cpo">PPO CPO LEVEL</option>
        <option value="rmfb_tbl">RMFB PMFC LEVEL</option>
        <option value="occidental_cps">Occidental Mindoro MPS</option>
        <option value="oriental_cps">Oriental Mindoro MPS</option>
        <option value="marinduque_cps">Marinduque MPS</option>
        <option value="romblon_cps">Romblon MPS</option>
        <option value="palawan_cps">Palawan MPS</option>
        <option value="puertop_cps">Puerto Princesa MPS</option>
      </select>

      <button
        class="btn btn-success d-flex gap-2 align-items-center shadow"
        @click.prevent="getRatePerRanking"
      >
        <i class="fa-solid fa-magnifying-glass"></i>
        Find
      </button>
    </div>
  </div>
</template>

<script>
import axios from "axios";
export default {
  data() {
    return {
      month: "",
      currentMonth: "",
      year: 0,
      level: "ppo_cpo",
      rateRanking: [],
      selectedText: "PPO",
    };
  },

  async mounted() {
    await this.getRatePerRanking();
  },

  created() {
    this.year = new Date().getFullYear();
    let currentDate = new Date();
    currentDate.setMonth(currentDate.getMonth() - 1);
    this.month = currentDate.toLocaleString("default", { month: "long" });
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

    async getRatePerRanking() {
      try {
        const selectElement = document.querySelector("#selectedTable3");
        const selectedOption =
          selectElement.options[selectElement.selectedIndex];
        this.selectedText = selectedOption.text;
        this.currentMonth = this.month;

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
.adminBox {
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
  .userDashboard {
    grid-template-columns: repeat(1, 300px);
    grid-template-rows: repeat(8, 300px);
    gap: 2rem;
    justify-content: center;
  }
}
</style>
