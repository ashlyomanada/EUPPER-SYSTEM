<template>
  <div class="table-data">
    <div class="order">
      <div class="table-options">
        <h4>User Ratings</h4>
        <div class="t-options">
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
          <select v-model="selectedTable" class="month" name="month" required>
            <option value="ppo_cpo">PPO CPO LEVEL</option>
            <option value="rmfb_tbl">RMFB PMFC LEVEL</option>
            <option value="occidental_cps">Occidental Mindoro MPS</option>
            <option value="oriental_cps">Oriental Mindoro MPS</option>
            <option value="marinduque_cps">Marinduque MPS</option>
            <option value="romblon_cps">Romblon MPS</option>
            <option value="palawan_cps">Palawan MPS</option>
            <option value="puertop_cps">Puerto Princesa MPS</option>
          </select>

          <label class="d-flex align-items-center" for="ratingYear"
            >Select Year</label
          >
          <input
            style="background: var(--light); color: var(--dark)"
            required
            class="form-control"
            type="text"
            id="ratingYear"
            v-model="year"
          />
          <button
            @click="fetchDataByTbl"
            class="btn btn-primary d-flex align-items-center"
          >
            <i class="bx bx-search"></i>Find
          </button>
        </div>
      </div>
      <table v-if="dataFetched && userRatings.length > 0">
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
        <tbody>
          <tr v-for="(rating, index) in paginatedRatings" :key="index">
            <td class="t-rateData">
              <button
                class="pen-btn btn btn-primary"
                @click="editRating(index)"
              >
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

  <!-- Bootstrap Modal for Editing Rating -->
  <div
    class="modal fade"
    id="editRatingModal"
    tabindex="-1"
    aria-labelledby="editRatingModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div
        class="modal-content text-start"
        style="background: var(--light); color: var(--dark)"
      >
        <div class="modal-header">
          <h5 class="modal-title" id="editRatingModalLabel">Edit Rating</h5>
        </div>
        <div class="modal-body text-start">
          <form @submit.prevent="saveUserRates">
            <div class="mb-3">
              <label class="form-label text-start" for="editMonth"
                >Month:</label
              >
              <select
                class="form-control"
                v-model="selectedRating.month"
                required
              >
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
            </div>
            <div class="mb-3">
              <label class="form-label text-start" for="editYear">Year:</label>
              <input
                style="background: var(--light); color: var(--dark)"
                required
                type="text"
                class="form-control"
                id="editYear"
                v-model="selectedRating.year"
              />
            </div>
            <!-- Loop through columns to dynamically render input fields -->
            <template v-for="(column, colIndex) in columns" :key="colIndex">
              <div class="mb-3">
                <label class="form-label" :for="`edit${column}`">{{
                  column.replace(/_/g, " ")
                }}</label>
                <input
                  style="background: var(--light); color: var(--dark)"
                  required
                  type="number"
                  class="form-control"
                  :id="`edit${column}`"
                  v-model="selectedRating[column]"
                  step="any"
                  :max="userMaxRate"
                />
              </div>
            </template>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">
                Save changes
              </button>
              <button
                type="button"
                class="btn btn-danger"
                data-bs-dismiss="modal"
              >
                Close
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="alert-container" style="z-index: 2000">
    <v-alert v-if="alertMessage" :type="errorType" class="error-message">{{
      alertMessage
    }}</v-alert>
  </div>
</template>

<script>
import axios from "axios";
import { Modal } from "bootstrap";

export default {
  data() {
    return {
      userRatings: [],
      columns: [],
      selectedUser: 54,
      allUsersName: [],
      selectedTable: "ppo_cpo",
      dataFetched: false,
      selectedRating: {},
      editRatingModal: null,
      currentPage: 1,
      itemsPerPage: 12,
      alertMessage: "",
      errorType: "",
      year: new Date().getFullYear(),
      userMaxRate: 0,
      selectedId: 0,
    };
  },

  async mounted() {
    this.fetchColumns();
    this.getUsername();
    await this.fetchDataByTbl();
    await this.getUserData();
  },

  computed: {
    totalPages() {
      return Math.ceil(this.userRatings.length / this.itemsPerPage);
    },
    paginatedRatings() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.userRatings.slice(start, end);
    },
  },

  methods: {
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
        // console.log(this.allUsersName);
      } catch (error) {
        console.error("Error fetching users:", error);
      }
    },

    async fetchDataByTbl() {
      try {
        const userId = this.selectedUser;
        if (userId) {
          const response = await axios.post(`/viewUserByTblRates`, {
            User: this.selectedUser,
            TableName: this.selectedTable,
            Year: this.year,
          });

          this.selectedId = response.data[0].userid;
          // console.log(response.data[0].userid);

          if (response.status === 200) {
            this.userRatings = response.data;
            this.dataFetched = true;
            this.fetchColumnPerTbl();
            this.getUserData();
          } else {
            this.dataFetched = false;
            console.error(`Failed to fetch data. Status: ${response.status}`);
          }
        } else {
          console.error("User id is required");
        }
      } catch (error) {
        console.error("Error fetching data:", error);
        this.dataFetched = false;
      }
    },

    async getUserData() {
      const userId = this.selectedId;
      const response = await axios.get(`/getUserData/${userId}`);
      this.userMaxRate = response.data.maxRate;
      // console.log(this.userMaxRate);
    },

    editRating(index) {
      // Calculate the correct index in the full userRatings array
      const globalIndex = (this.currentPage - 1) * this.itemsPerPage + index;

      // Now, use this globalIndex to fetch the correct item
      this.selectedRating = { ...this.userRatings[globalIndex] };

      // Show the modal
      this.editRatingModal = new Modal(
        document.getElementById("editRatingModal")
      );
      this.editRatingModal.show();
    },

    async saveUserRates() {
      try {
        if (!this.selectedRating.id) {
          console.error("Rating ID is required.");
          return;
        }

        const requestData = {
          ...this.selectedRating,
          TableName: this.selectedTable,
        };

        const response = await axios.post(`/updateUserRating`, requestData);

        if (response.data.success) {
          console.log("Rating updated successfully!");
          this.editRatingModal.hide();
          this.fetchDataByTbl();
          this.alertMessage = "Rating updated successfully";
          this.errorType = "success";

          setTimeout(() => {
            this.alertMessage = null;
            this.errorType = null;
          }, 3000);
        } else {
          console.error("Failed to update rating.");
        }
      } catch (error) {
        console.error("Error updating rating:", error);
        if (error.response.status === 404) {
          this.alertMessage = "Nothing to update";
          this.errorType = "error";

          setTimeout(() => {
            this.alertMessage = null;
            this.errorType = null;
          }, 3000);
        } else if (error.response.status === 500) {
          this.alertMessage = "Server error, please try again later";
          this.errorType = "error";

          setTimeout(() => {
            this.alertMessage = null;
            this.errorType = null;
          }, 3000);
        } else if (error.response.status === 400) {
          this.alertMessage =
            "The month or year you entered is already exists.";
          this.errorType = "error";

          setTimeout(() => {
            this.alertMessage = null;
            this.errorType = null;
          }, 3000);
        } else {
          this.alertMessage = "Please check your internet connection";
          this.errorType = "error";

          setTimeout(() => {
            this.alertMessage = null;
            this.errorType = null;
          }, 3000);
        }
      }
    },

    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
      }
    },
  },
};
</script>

<style>
.labels {
  color: var(--dark);
}
.dim-overlay {
  position: fixed;
  display: flex;
  justify-content: center;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  overflow-y: auto;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1;
}
.t-rate {
  padding: 0 1rem;
}
.t-rateData {
  text-align: center;
}

.table-options {
  display: flex;
  justify-content: space-between;
  margin-bottom: 2rem;
  width: 100%;
  flex-direction: column;
}

.t-options {
  display: flex;
  gap: 1rem;
}

.pagination {
  margin-top: 20px;
}

#ratingYear {
  width: 10%;
}

@media screen and (max-width: 1100px) {
  .table-options {
    width: 100vw;
  }
}

@media screen and (max-width: 600px) {
  #ratingYear {
    width: 20vw;
  }
}
</style>
