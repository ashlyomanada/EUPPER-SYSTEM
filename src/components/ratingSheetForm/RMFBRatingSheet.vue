<template>
  <div
    class="table-data"
    :style="{ display: buttonVisible ? 'block' : 'none' }"
  >
    <div class="order">
      <div class="rating-header">
        <div class="rating-subheader">
          <h3>Unit Performance Evaluation Rating</h3>
          <h4 class="head-subtitle">RMFB / PMFC Level</h4>
        </div>
      </div>
      <form @submit.prevent="saveRating" class="ratingsheet-container">
        <div class="rateDate">
          <select class="rateMonth" v-model="Month" name="month">
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
          <input
            type="number"
            class="rateYear"
            name="year"
            min="2000"
            max="2100"
            step="1"
            placeholder="Year"
            v-model="Year"
            required
          />
        </div>
        <div
          v-for="(column, index) in columns"
          :key="index"
          style="width: 100%; display: flex; justify-content: center"
        >
          <input
            type="number"
            class="rateInput"
            :placeholder="getPlaceholder(column)"
            v-model="formData[column]"
            required
            min="0"
            :max="userMaxRate"
            step="any"
          />
        </div>
        <button class="submitPPORate" type="submit">Submit</button>
      </form>
    </div>
  </div>
  <div
    class="modal fade"
    id="rmfbSuccessModal"
    tabindex="-1"
    aria-labelledby="successModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-body text-center">
          <img
            v-if="success"
            class="checkImg"
            src="./img/check2.gif"
            alt="Success"
          />
          <h1 class="alertContent">{{ alertMessage }}</h1>
          <button class="btn btn-primary" @click.prevent="okayBtn">Okay</button>
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
      selectedRating: null,
      buttonVisible: true,
      formVisible: false,
      UserId: "",
      Month: "",
      Year: "",
      columns: [],
      formData: {},
      office: "",
      alertMessage: "",
      success: false,
      userMaxRate: 0,
    };
  },

  created() {
    this.fetchColumns();
    this.fetchUserData();
    const currentDate = new Date();
    this.Month = currentDate.toLocaleString("default", {
      month: "long",
    });
    this.Year = currentDate.getFullYear();
  },

  async mounted() {
    await this.getMaxRateByUser();
  },
  methods: {
    async getMaxRateByUser() {
      const userId = sessionStorage.getItem("id");
      try {
        const response = await axios.post("/getMaxRateByUser", {
          UserId: userId,
        });
        this.userMaxRate = response.data.maxRate;
      } catch (error) {
        console.log(error);
      }
    },

    async fetchUserData() {
      const storedUserId = sessionStorage.getItem("id");
      if (storedUserId) {
        try {
          const response = await axios.get(`/getUserData/${storedUserId}`);
          if (response.status === 200) {
            const userData = response.data;
            this.office = userData.office;
            //console.log(this.office);
          } else {
            console.error(`Unexpected response status: ${response.status}`);
          }
        } catch (e) {
          console.log(e);
        }
      }
    },
    async fetchColumns() {
      try {
        const response = await axios.get("/getColumnNameRMFB");
        this.columns = response.data.filter(
          (column) => !["id", "userid", "month", "year"].includes(column)
        );
        this.columns.forEach((column) => {
          // Initialize formData with empty values for each column
          this.formData[column] = "";
        });
      } catch (error) {
        console.error("Error fetching column names:", error);
      }
    },

    async saveRating() {
      try {
        this.UserId = sessionStorage.getItem("id");
        const data = {
          UserId: this.UserId,
          Month: this.Month,
          Year: this.Year,
          ...this.formData, // Include formData properties
        };

        // Send data to server for insertion
        const response = await axios.post("/insertDataRMFB", data);

        const modalElement = document.getElementById("rmfbSuccessModal");
        const modalInstance = new Modal(modalElement);

        if (response.status === 200) {
          // Data successfully saved
          this.Month = "";
          this.Year = "";
          Object.keys(this.formData).forEach((key) => {
            this.formData[key] = "";
          });

          this.alertMessage = "Successfully Rated";
          this.success = true;
          modalInstance.show();

          setTimeout(() => {
            modalInstance.hide();
            this.success = false;
          }, 5000);
        }
      } catch (error) {
        const modalElement = document.getElementById("rmfbSuccessModal");
        const modalInstance =
          Modal.getInstance(modalElement) || new Modal(modalElement);

        if (error.response && error.response.status === 400) {
          this.alertMessage = "Record already exists";
        } else if (error.response && error.response.status === 500) {
          this.alertMessage = "Server error. Please try again later.";
        } else {
          this.alertMessage =
            "Please check your internet connection and try again later.";
        }

        modalInstance.show();
        setTimeout(() => {
          modalInstance.hide();
        }, 5000);
      }
    },

    okayBtn() {
      const modalElement = document.getElementById("rmfbSuccessModal");
      const modalInstance =
        Modal.getInstance(modalElement) || new Modal(modalElement);

      // Hide the modal
      modalInstance.hide();

      // Manually remove the backdrop if it still exists
      const backdrop = document.querySelector(".modal-backdrop");
      if (backdrop) {
        backdrop.remove();
      }
    },

    getPlaceholder(column) {
      return column.replace(/_/g, " ");
    },
  },
};
</script>
