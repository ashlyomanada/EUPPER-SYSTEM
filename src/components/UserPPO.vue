<template>
  <div class="table-data">
    <div class="order">
      <div class="head">
        <div>
          <h3>Unit Performance Evaluation Rating</h3>
          <h4>Oriental Mindoro PPO / CPO Level</h4>
        </div>
      </div>
      <table>
        <thead>
          <tr>
            <th>Month</th>
            <th>Year</th>
            <th>Office</th>
            <th>DO</th>
            <th>DIDM</th>
            <th>DI</th>
            <th>DPCR</th>
            <th>Dl</th>
            <th>Dhrdd</th>
            <th>Dprm</th>
            <th>Dictm</th>
            <th>Dpl</th>
            <th>Dc</th>
            <th>Drd</th>
          </tr>
          <tr v-if="dataFetched">
            <td>{{ month }}</td>
            <td>{{ year }}</td>
            <td>{{ office }}</td>
            <td>{{ Do }}</td>
            <td>{{ didm }}</td>
            <td>{{ di }}</td>
            <td>{{ dpcr }}</td>
            <td>{{ dl }}</td>
            <td>{{ dhrdd }}</td>
            <td>{{ dprm }}</td>
            <td>{{ dictm }}</td>
            <td>{{ dpl }}</td>
            <td>{{ dc }}</td>
            <td>{{ drd }}</td>
          </tr>
          <tr v-else>
            <h2>No Ratings Yet</h2>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</template>

<script>
import axios from "axios";
export default {
  data() {
    return {
      dataFetched: false,
      month: "", // Use lowercase for property names
      year: "",
      office: "",
      Do: "",
      didm: "",
      di: "",
      dpcr: "",
      dl: "",
      dhrdd: "",
      dprm: "",
      dictm: "",
      dpl: "",
      dc: "",
      drd: "",
    };
  },

  mounted() {
    this.fetchData();
  },

  methods: {
    fetchData() {
      const storedUserId = sessionStorage.getItem("id");

      // Check if the user is logged in
      if (storedUserId) {
        // Make an Axios request to fetch user data based on session ID
        axios
          .get(`/viewUserRatings/${storedUserId}`)
          .then((response) => {
            // Update the component's data with the fetched user data
            console.log(response.data);
            const userRatings = response.data;
            this.month = userRatings.month;
            this.year = userRatings.year;
            this.office = userRatings.office;
            this.Do = userRatings.do;
            this.didm = userRatings.didm;
            this.di = userRatings.di;
            this.dpcr = userRatings.dpcr;
            this.dl = userRatings.dl;
            this.dhrdd = userRatings.dhrdd;
            this.dprm = userRatings.dprm;
            this.dictm = userRatings.dictm;
            this.dpl = userRatings.dpl;
            this.dc = userRatings.dc;
            this.drd = userRatings.drd;

            // Set dataFetched to true to indicate that the data is available
            this.dataFetched = true;
          })
          .catch((error) => {
            console.error("Error fetching user data:", error);
          });
      }
    },
  },
};
</script>

<style></style>
