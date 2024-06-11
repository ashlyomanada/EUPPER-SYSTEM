<template>
  <div class="table-data">
    <div class="order">
      <div id="evaluation-ratings">
        <div class="table-options">
          <div class="options-control d-flex align-items-center mb-10">
            <div class="w-25 d-flex">
              <h4>User Ratings</h4>
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
              <button
                @click="fetchDataByTbl"
                class="find d-flex align-items-center"
              >
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
              <input
                style="background: var(--light); color: var(--dark)"
                required
                type="text"
                class="form-control"
                id="editMonth"
                v-model="selectedRating.month"
              />
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
    };
  },

  created() {
    this.fetchColumns();
    this.getUsername();
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
          });

          if (response.status === 200) {
            this.userRatings = response.data;
            this.dataFetched = true;
            this.fetchColumnPerTbl();
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

    editRating(index) {
      this.selectedRating = { ...this.userRatings[index] };
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
</style>
