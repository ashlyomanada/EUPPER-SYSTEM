<template>
  <div class="table-data">
    <div class="order">
      <div id="evaluation-ratings">
        <div class="table-options">
          <div class="options-control">
            <div class="w-25 d-flex">
              <h3>User Ratings</h3>
            </div>
            <div class="w-75 d-flex justify-content-end gap-2">
              <label class="d-flex align-items-center" for="month"
                >Select User:</label
              >
              <select class="month" name="month" v-model="selectedUser">
                <option
                  v-for="user in allUsersName"
                  :key="user.user_id"
                  :value="user.user_id"
                >
                  {{ user.username }}
                </option>
              </select>
              <label class="d-flex align-items-center" for="month"
                >Select Table:</label
              >
              <select
                v-model="selectedTable"
                class="month"
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
              <button @click="fetchDataByTbl" class="find">
                <i class="bx bx-search"></i>Find
              </button>
            </div>
          </div>
        </div>
      </div>
      <table>
        <thead>
          <tr>
            <th class="t-rate">Action</th>
            <th class="t-rate">Month</th>
            <th class="t-rate">Year</th>
            <th v-for="(column, index) in columns" :key="index" class="t-rate">
              {{ column.replace(/_/g, " ") }}
            </th>
          </tr>
        </thead>
        <tbody v-if="dataFetched && userRatings.length > 0">
          <tr v-for="(rating, index) in userRatings" :key="index">
            <td class="t-rateData">
              <button class="pen-btn" @click="editRating(index)">
                <i class="fa-solid fa-pen fa-lg"></i>
              </button>
            </td>
            <td class="t-rateData">{{ rating.month }}</td>
            <td class="t-rateData">{{ rating.year }}</td>
            <td
              v-for="(column, colIndex) in columns"
              :key="colIndex"
              class="t-rateData"
            >
              {{ rating[column] }}
            </td>
          </tr>
        </tbody>
      </table>
      <h5 v-if="!dataFetched" style="text-align: center">No Ratings Found</h5>
    </div>
  </div>
  <div class="modal-background" :class="{ 'dim-overlay': formVisible }">
    <form
      class="form"
      id="rating-form-edit"
      :style="{ display: formVisible ? 'block' : 'none' }"
    >
      <div class="form-edit">
        <label for="editMonth">Month:</label>
        <input type="text" id="editMonth" v-model="selectedRating.month" />

        <label for="editYear">Year:</label>
        <input type="text" id="editYear" v-model="selectedRating.year" />

        <!-- Loop through columns to dynamically render input fields -->
        <template v-for="(column, colIndex) in columns" :key="colIndex">
          <label :for="`edit${column}`">{{ column.replace(/_/g, " ") }}</label>
          <input :type="number" v-model="selectedRating[column]" />
        </template>

        <div class="modal-buttons">
          <button @click.prevent="saveUserRates">Save</button>
          <button @click.prevent="closeForm">Close</button>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      userRatings: [],
      columns: [],
      selectedUser: 54,
      allUsersName: [],
      dataFetched: false,
      formVisible: false,
      selectedRating: {},
    };
  },

  created() {
    this.fetchColumns();
    this.getUsername();
    this.fetchData();
  },

  methods: {
    closeForm() {
      this.formVisible = false;
    },
    async fetchColumns() {
      try {
        const response = await axios.get("/getColumnNamePPO");
        this.columns = response.data.filter(
          (column) => !["id", "userid", "month", "year"].includes(column)
        );
      } catch (error) {
        console.error("Error fetching column names:", error);
      }
    },

    async fetchColumnPerTbl() {
      try {
        const response = await axios.post("/getColumnNamePerTbl", {
          TableName: this.selectedTable,
        });
        this.columns = response.data.filter(
          (column) => !["id", "userid", "month", "year"].includes(column)
        );
      } catch (error) {
        console.error("Error fetching column names:", error);
      }
    },

    async getUsername() {
      try {
        const response = await axios.get("/getAllUsersName");
        this.allUsersName = response.data;
      } catch (error) {
        console.error("Error fetching users:", error);
      }
    },

    fetchData() {
      const userId = this.selectedUser;
      if (userId) {
        axios
          .get(`/viewUserPPORates/${userId}`)
          .then((response) => {
            this.userRatings = response.data;
            this.dataFetched = true;
          })
          .catch((error) => {
            console.error("Error fetching user data:", error);
          });
      }
    },

    async fetchDataByTbl() {
      try {
        const userId = this.selectedUser;
        if (userId) {
          const response = await axios.post(`/viewUserByTblRates`, {
            User: this.selectedUser,
            TableName: this.selectedTable,
          });

          if (response.status === 200) {
            this.userRatings = response.data;
            this.dataFetched = true;
            this.fetchColumnPerTbl();
          } else {
            // If response status is not 200 (success), set dataFetched to false
            this.dataFetched = false;
            console.error(`Failed to fetch data. Status: ${response.status}`);
          }
        } else {
          // Handle case where userId is not present
          console.error("User id is required");
        }
      } catch (error) {
        // Handle other errors (e.g., network error, server error)
        console.error("Error fetching data:", error);
        this.dataFetched = false;
      }
    },

    editRating(index) {
      // Set the selected rating data for editing
      this.selectedRating = { ...this.userRatings[index] };
      this.formVisible = true; // Show the edit form
    },

    async saveUserRates() {
      try {
        if (!this.selectedRating.id) {
          console.error("Rating ID is required.");
          return;
        }

        const requestData = {
          ...this.selectedRating,
          TableName: this.selectedTable, // Include TableName in the request data
        };

        const response = await axios.post(`/updateUserRating`, requestData);

        if (response.data.success) {
          console.log("Rating updated successfully!");
          this.formVisible = false; // Hide the form after successful update
          this.fetchDataByTbl(); // Refresh data after update
        } else {
          console.error("Failed to update rating.");
        }
      } catch (error) {
        console.error("Error updating rating:", error);
      }
    },
  },
};
</script>

<style>
.dim-overlay {
  position: fixed;
  display: flex;
  justify-content: center;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  overflow-y: auto;
  background: rgba(
    0,
    0,
    0,
    0.5
  ); /* Adjust the last value for the desired transparency */
  z-index: 1;
  /* Make sure the overlay is above other elements */
}
#rating-form-edit {
  position: absolute;
  width: 50%;
  top: 10%;
  left: 35%;
  display: none;
}
.form-edit {
  display: flex;
  flex-direction: column;
  height: 50%;
  gap: 0.5rem;
}
.evaluation-ratings {
  display: flex;
  align-items: center;
  grid-gap: 16px;
  margin-bottom: 24px;
  flex-direction: column;
}
.table-options {
  width: 100%;
  display: flex;
  align-items: center;
  padding: 1rem 0rem;
}
.options-control {
  width: 100%;
  display: flex;
  gap: 1rem;
}
.t-rate {
  padding: 0 1rem;
}
.t-rateData {
  text-align: center;
}
</style>
