<template>
  <div class="table-data">
    <div class="order">
      <div class="rating-header d-flex flex-column">
        <div class="rating-subheader">
          <h2>Unit Performance Evaluation Rating</h2>
          <h4 class="head-subtitle">Municipalities of Puerto Princesa</h4>
        </div>

        <form class="userViewContainer" @submit.prevent="fetchData">
          <label class="d-flex" for="year"> Select Year:</label>
          <input
            type="number"
            class="form-control"
            style="width: 30%"
            name="year"
            min="2000"
            max="2100"
            step="1"
            placeholder="Year"
            v-model="year"
          />
          <button
            class="btn btn-primary d-flex align-items-center"
            type="submit"
          >
            <i class="bx bx-search"></i>Find
          </button>
        </form>
      </div>
      <table v-if="dataFetched">
        <thead>
          <tr>
            <th class="t-rate">Action</th>
            <th>Month</th>
            <th>Year</th>
            <th v-for="(column, index) in columns" :key="index" class="t-row">
              {{ column.replace(/_/g, " ") }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(rating, index) in paginatedRatings" :key="index">
            <td class="t-rateData">
              <button
                class="pen-btn btn btn-primary text-light"
                @click="editRating(index)"
              >
                <i class="fa-solid fa-pen fa-lg"></i>
              </button>
            </td>
            <td>{{ rating.month }}</td>
            <td>{{ rating.year }}</td>
            <td v-for="(column, colIndex) in columns" :key="colIndex">
              {{ rating[column] }}
            </td>
          </tr>
        </tbody>
      </table>
      <h4 v-else style="text-align: center">No Ratings Yet</h4>
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
      dataFetched: false,
      userRatings: [],
      columns: [],
      currentPage: 1,
      itemsPerPage: 12,
      year: null,
      selectedRating: {},
      selectedTable: "puertop_cps",
      alertMessage: "",
      errorType: "",
      userMaxRate: 0,
    };
  },

  mounted() {
    this.fetchData();
    this.fetchColumns();
    this.getUserData();
  },

  created() {
    const year = new Date().getFullYear();
    this.year = year;
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
    async getUserData() {
      const userId = sessionStorage.getItem("id");
      const response = await axios.get(`/getUserData/${userId}`);
      this.userMaxRate = response.data.maxRate;
    },

    async fetchColumns() {
      try {
        const response = await axios.get("/getColumnNamePuer");
        this.columns = response.data.filter(
          (column) => !["id", "userid", "month", "year"].includes(column)
        );
      } catch (error) {
        console.error("Error fetching column names:", error);
      }
    },

    fetchData() {
      const storedUserId = sessionStorage.getItem("id");
      const level = "puertop_cps";
      if (storedUserId) {
        axios
          .get(`/viewUserRates/${storedUserId}/${this.year}/${level}`)
          .then((response) => {
            this.userRatings = response.data;
            this.dataFetched = true;
          })
          .catch((error) => {
            console.error("Error fetching user data:", error);
            if (error.response.status === 404) {
              this.dataFetched = false;
            }
          });
      }
    },

    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
      }
    },

    editRating(index) {
      const globalIndex = (this.currentPage - 1) * this.itemsPerPage + index;
      this.selectedRating = { ...this.userRatings[globalIndex] };
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
          this.fetchData();
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
.pagination {
  margin-top: 20px;
}
</style>
