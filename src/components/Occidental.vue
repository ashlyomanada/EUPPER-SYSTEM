<template>
  <div>
    <div class="table-data" v-if="visible">
      <div class="order">
        <div class="rating-header">
          <div>
            <h2>Occidental Mindoro PPO Ratings</h2>
            <h4 class="head-subtitle">MPS / CPS Level</h4>
          </div>
        </div>
        <table v-if="dataFetched">
          <thead>
            <tr>
              <th class="t-row">Month</th>
              <th class="t-row">Year</th>
              <th class="t-row">Abra</th>
              <th class="t-row">Calintaan</th>
              <th class="t-row">Looc</th>
              <th class="t-row">Lubang</th>
              <th class="t-row">Magsaysay</th>
              <th class="t-row">Mamburao</th>
              <th class="t-row">Paluan</th>
              <th class="t-row">Rizal</th>
              <th class="t-row">Sablayan</th>
              <th class="t-row">San Jose</th>
              <th class="t-row">Santa Cruz</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="rating in usersRate" :key="rating.userid">
              <td class="t-data">{{ rating.month }}</td>
              <td class="t-data">{{ rating.year }}</td>
              <td class="t-data">{{ rating.abra }}</td>
              <td class="t-data">{{ rating.calintaan }}</td>
              <td class="t-data">{{ rating.looc }}</td>
              <td class="t-data">{{ rating.lubang }}</td>
              <td class="t-data">{{ rating.magsaysay }}</td>
              <td class="t-data">{{ rating.mamburao }}</td>
              <td class="t-data">{{ rating.paluan }}</td>
              <td class="t-data">{{ rating.rizal }}</td>
              <td class="t-data">{{ rating.sablayan }}</td>
              <td class="t-data">{{ rating.san_jose }}</td>
              <td class="t-data">{{ rating.sta_cruz }}</td>
            </tr>
          </tbody>
        </table>
        <h4 v-else style="text-align: center">No Ratings Yet</h4>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      usersRate: "",
      dataFetched: false,
      visible: true,
      number: 0,
    };
  },
  components: {},
  mounted() {
    this.fetchUserData();
  },
  methods: {
    async fetchUserData() {
      try {
        const storedUserId = sessionStorage.getItem("id");
        if (storedUserId) {
          const response = await axios.get(
            `/viewUserOcciRates/${storedUserId}`
          );
          this.usersRate = response.data;
          this.dataFetched = true;
          // console.log(this.usersRate);
        }
      } catch (e) {
        console.log(e);
      }
    },
  },
};
</script>

<style>
.btn-container {
  width: 100%;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  padding: 0.5rem 1rem;
  gap: 1rem;
}
.nextBtn {
  background: rgb(26, 94, 182);
  padding: 0.5rem 1rem;
  color: white;
  border-radius: 0.5rem;
  display: flex;
  right: 0;
}
</style>
