<template>
  <div v-if="visible">
    <div class="table-data">
      <div class="order">
        <div class="rating-header">
          <div>
            <h2>Oriental Mindoro PPO Ratings</h2>
            <h4 class="head-subtitle">MPS / CPS Level</h4>
          </div>
        </div>
        <table v-if="dataFetched">
          <thead>
            <tr>
              <th class="t-row">Month</th>
              <th class="t-row">Year</th>
              <th class="t-row">Baco</th>
              <th class="t-row">Bansud</th>
              <th class="t-row">Bongabong</th>
              <th class="t-row">Bulalacao</th>
              <th class="t-row">Calapan</th>
              <th class="t-row">Gloria</th>
              <th class="t-row">Mansalay</th>
              <th class="t-row">Naujan</th>
              <th class="t-row">Pinamalayan</th>
              <th class="t-row">Pola</th>
              <th class="t-row">PuertoGalera</th>
              <th class="t-row">Roxas</th>
              <th class="t-row">SanTeodoro</th>
              <th class="t-row">Socorro</th>
              <th class="t-row">Victoria</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="rating in usersRate" :key="rating.userid">
              <td class="t-data">{{ rating.month }}</td>
              <td class="t-data">{{ rating.year }}</td>
              <td class="t-data">{{ rating.baco }}</td>
              <td class="t-data">{{ rating.bansud }}</td>
              <td class="t-data">{{ rating.bongabong }}</td>
              <td class="t-data">{{ rating.bulalacao }}</td>
              <td class="t-data">{{ rating.calapan }}</td>
              <td class="t-data">{{ rating.gloria }}</td>
              <td class="t-data">{{ rating.mansalay }}</td>
              <td class="t-data">{{ rating.naujan }}</td>
              <td class="t-data">{{ rating.pinamalayan }}</td>
              <td class="t-data">{{ rating.pola }}</td>
              <td class="t-data">{{ rating.puerto_galera }}</td>
              <td class="t-data">{{ rating.roxas }}</td>
              <td class="t-data">{{ rating.san_teodoro }}</td>
              <td class="t-data">{{ rating.soccorro }}</td>
              <td class="t-data">{{ rating.victoria }}</td>
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
      componentName: "",
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
            `/viewUserOrienRates/${storedUserId}`
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

<style></style>
