<template>
  <div class="table-data">
    <div class="order">
      <div class="rating-header">
        <div>
          <h2>Unit Performance Evaluation Rating</h2>
          <h4 class="head-subtitle">PPO / CPO Level</h4>
        </div>
      </div>
      <table v-if="dataFetched">
        <thead>
          <tr>
            <th class="t-row">Month</th>
            <th class="t-row">Year</th>
            <th class="t-row">Occidental Mindoro</th>
            <th class="t-row">Oriental Mindoro</th>
            <th class="t-row">Marinduque</th>
            <th class="t-row">Romblon</th>
            <th class="t-row">Palawan</th>
            <th class="t-row">Puerto Prinsesa</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="rating in userRatings" :key="rating.userid">
            <td class="t-data">{{ rating.month }}</td>
            <td class="t-data">{{ rating.year }}</td>
            <td class="t-data">{{ rating.occi }}</td>
            <td class="t-data">{{ rating.ormin }}</td>
            <td class="t-data">{{ rating.marin }}</td>
            <td class="t-data">{{ rating.rom }}</td>
            <td class="t-data">{{ rating.pal }}</td>
            <td class="t-data">{{ rating.puertop }}</td>
          </tr>
        </tbody>
      </table>
      <h4 v-else style="text-align: center">No Ratings Yet</h4>
    </div>
  </div>
</template>

<script>
import axios from "axios";
export default {
  data() {
    return {
      UsersInfo: "",
      dataFetched: false,
      userRatings: [],
      formVisible: false,
      selectedRatings: {
        id: null,
        Occidental: "",
        Oriental: "",
        Marinduque: "",
        Romblon: "",
        Palawan: "",
        PuertoPrinsesa: "",
      },
    };
  },

  mounted() {
    this.fetchData();
  },

  methods: {
    fetchData() {
      const storedUserId = sessionStorage.getItem("id");

      if (storedUserId) {
        axios
          .get(`/viewUserPPORates/${storedUserId}`)
          .then((response) => {
            this.userRatings = response.data; // Store all ratings in the array
            //console.log(this.userRatings);
            // Set dataFetched to true to indicate that the data is available
            this.dataFetched = true;
          })
          .catch((error) => {
            console.error("Error fetching user data:", error);
          });
      }
    },

    openForm(UserRatings) {
      this.selectedRatings = { ...UserRatings };
      this.formVisible = true;
    },
  },
};
</script>

<style>
#rating-form-edit2 {
  position: absolute;
  width: 80%;
  top: 15%;
  left: 10%;
  display: none;
  background-color: var(--grey);
}
.form-edit {
  display: flex;
  gap: 2rem;
  align-items: center;
}
.pen-btn {
  color: rgb(233, 70, 70);
}
</style>
