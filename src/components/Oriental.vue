<template>
  <div class="table-data">
    <div class="order">
      <div class="rating-header">
        <div>
          <h2>Unit Performance Evaluation Rating</h2>
          <h4 class="head-subtitle">Municipalities of Oriental Mindoro</h4>
        </div>
      </div>
      <table v-if="dataFetched">
        <thead>
          <tr>
            <th class="t-row">Month</th>
            <th class="t-row">Year</th>
            <th v-for="(column, index) in columns" :key="index" class="t-row">
              {{ column.replace(/_/g, " ") }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(rating, index) in userRatings" :key="index">
            <td class="t-data">{{ rating.month }}</td>
            <td class="t-data">{{ rating.year }}</td>
            <td
              v-for="(column, colIndex) in columns"
              :key="colIndex"
              class="t-data"
            >
              {{ rating[column] }}
            </td>
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
      dataFetched: false,
      userRatings: [],
      columns: [],
    };
  },

  mounted() {
    this.fetchData();
    this.fetchColumns();
  },

  methods: {
    async fetchColumns() {
      try {
        const response = await axios.get("/getColumnNameOrmin");
        this.columns = response.data.filter(
          (column) => !["id", "userid", "month", "year"].includes(column)
        );
      } catch (error) {
        console.error("Error fetching column names:", error);
      }
    },

    fetchData() {
      const storedUserId = sessionStorage.getItem("id");
      if (storedUserId) {
        axios
          .get(`/viewUserOrienRates/${storedUserId}`)
          .then((response) => {
            this.userRatings = response.data;
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
