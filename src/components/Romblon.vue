<template>
  <div v-if="visible">
    <div class="table-data">
      <div class="order">
        <div class="rating-header">
          <div>
            <h2>Romblon PPO Ratings</h2>
            <h4 class="head-subtitle">MPS / CPS Level</h4>
          </div>
        </div>
        <table v-if="dataFetched">
          <thead class="thead-romblon">
            <tr>
              <th class="t-row">Month</th>
              <th class="t-row">Year</th>
              <th class="t-row">Alcantara</th>
              <th class="t-row">Banton</th>
              <th class="t-row">Cajidiocan</th>
              <th class="t-row">Calatrava</th>
              <th class="t-row">Concepcion</th>
              <th class="t-row">Concuera</th>
              <th class="t-row">Ferrol</th>
              <th class="t-row">Looc</th>
              <th class="t-row">Magdiwang</th>
              <th class="t-row">Odiongan</th>
              <th class="t-row">Romblon</th>
              <th class="t-row">SanAgustin</th>
              <th class="t-row">SanAndres</th>
              <th class="t-row">SanFernando</th>
              <th class="t-row">SanJose</th>
              <th class="t-row">StaFe</th>
              <th class="t-row">StaMaria</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="rating in usersRate" :key="rating.userid">
              <td class="t-data">{{ rating.month }}</td>
              <td class="t-data">{{ rating.year }}</td>
              <td class="t-data">{{ rating.alcantara }}</td>
              <td class="t-data">{{ rating.banton }}</td>
              <td class="t-data">{{ rating.cajidiocan }}</td>
              <td class="t-data">{{ rating.calatrava }}</td>
              <td class="t-data">{{ rating.concepcion }}</td>
              <td class="t-data">{{ rating.concuera }}</td>
              <td class="t-data">{{ rating.ferrol }}</td>
              <td class="t-data">{{ rating.looc }}</td>
              <td class="t-data">{{ rating.magdiwang }}</td>
              <td class="t-data">{{ rating.odiongan }}</td>
              <td class="t-data">{{ rating.romblon }}</td>
              <td class="t-data">{{ rating.san_agustin }}</td>
              <td class="t-data">{{ rating.san_andres }}</td>
              <td class="t-data">{{ rating.san_fernando }}</td>
              <td class="t-data">{{ rating.san_jose }}</td>
              <td class="t-data">{{ rating.sta_fe }}</td>
              <td class="t-data">{{ rating.sta_maria }}</td>
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
            `/viewUserRombRates/${storedUserId}`
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
.t-row {
  padding: 0.8rem;
}
.t-data {
  text-align: center;
}
</style>
