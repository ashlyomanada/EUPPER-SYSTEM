<template>
  <div v-if="visible">
    <div class="table-data">
      <div class="order">
        <div class="rating-header">
          <div>
            <h2>Palawan PPO Ratings</h2>
            <h4 class="head-subtitle">MPS / CPS Level</h4>
          </div>
        </div>
        <table v-if="dataFetched">
          <thead>
            <tr>
              <th class="t-row">Month</th>
              <th class="t-row">Year</th>
              <th class="t-row">Aborian</th>
              <th class="t-row">Agutaya</th>
              <th class="t-row">Arceli</th>
              <th class="t-row">Balabac</th>
              <th class="t-row">Bataraza</th>
              <th class="t-row">BrookesPoint</th>
              <th class="t-row">Busuanga</th>
              <th class="t-row">Cagayancilo</th>
              <th class="t-row">Coron</th>
              <th class="t-row">Culion</th>
              <th class="t-row">Cuyo</th>
              <th class="t-row">Dumaron</th>
              <th class="t-row">ElNido</th>
              <th class="t-row">Espanola</th>
              <th class="t-row">Kalayaan</th>
              <th class="t-row">Linapacan</th>
              <th class="t-row">Magsaysay</th>
              <th class="t-row">Narra</th>
              <th class="t-row">Quezon</th>
              <th class="t-row">Rizal</th>
              <th class="t-row">Roxas</th>
              <th class="t-row">SanVicente</th>
              <th class="t-row">Taytay</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="rating in usersRate" :key="rating.userid">
              <td class="t-data">{{ rating.month }}</td>
              <td class="t-data">{{ rating.year }}</td>
              <td class="t-data">{{ rating.aborian }}</td>
              <td class="t-data">{{ rating.agutaya }}</td>
              <td class="t-data">{{ rating.arceli }}</td>
              <td class="t-data">{{ rating.balabac }}</td>
              <td class="t-data">{{ rating.bataraza }}</td>
              <td class="t-data">{{ rating.brookes_point }}</td>
              <td class="t-data">{{ rating.busuanga }}</td>
              <td class="t-data">{{ rating.cagayancilo }}</td>
              <td class="t-data">{{ rating.coron }}</td>
              <td class="t-data">{{ rating.culion }}</td>
              <td class="t-data">{{ rating.cuyo }}</td>
              <td class="t-data">{{ rating.dumaron }}</td>
              <td class="t-data">{{ rating.el_nido }}</td>
              <td class="t-data">{{ rating.espanola }}</td>
              <td class="t-data">{{ rating.kalayaan }}</td>
              <td class="t-data">{{ rating.linapacan }}</td>
              <td class="t-data">{{ rating.magsaysay }}</td>
              <td class="t-data">{{ rating.narra }}</td>
              <td class="t-data">{{ rating.quezon }}</td>
              <td class="t-data">{{ rating.rizal }}</td>
              <td class="t-data">{{ rating.roxas }}</td>
              <td class="t-data">{{ rating.san_vicente }}</td>
              <td class="t-data">{{ rating.taytay }}</td>
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
          const response = await axios.get(`/viewUserPalRates/${storedUserId}`);
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
