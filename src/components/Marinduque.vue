<template>
  <div v-if="visible">
    <div class="table-data">
      <div class="order">
        <div class="rating-header">
          <div>
            <h2>Marinduque PPO Ratings</h2>
            <h4 class="head-subtitle">MPS / CPS Level</h4>
          </div>
        </div>
        <table v-if="dataFetched">
          <thead>
            <tr>
              <th>Month</th>
              <th>Year</th>
              <th>Boac</th>
              <th>Buenavista</th>
              <th>Gasan</th>
              <th>Mogpog</th>
              <th>StaCruz</th>
              <th>Torrijos</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="rating in usersRate" :key="rating.userid">
              <td>{{ rating.month }}</td>
              <td>{{ rating.year }}</td>
              <td>{{ rating.boac }}</td>
              <td>{{ rating.buenavista }}</td>
              <td>{{ rating.gasan }}</td>
              <td>{{ rating.mogpog }}</td>
              <td>{{ rating.sta_cruz }}</td>
              <td>{{ rating.torrijos }}</td>
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
            `/viewUserMarinRates/${storedUserId}`
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
