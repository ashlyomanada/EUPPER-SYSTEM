<template>
  <div
    class="table-data"
    :style="{ display: buttonVisible ? 'block' : 'none' }"
  >
    <div class="order">
      <div class="rating-header">
        <div class="rating-subheader">
          <h3>Unit Performance Evaluation Rating</h3>
          <h4 class="head-subtitle">PPO / CPO Level</h4>
        </div>
      </div>
      <form @submit.prevent="saveRating" class="ratingsheet-container">
        <div class="rateDate">
          <select class="form-control text-center" v-model="Month" name="month">
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
            class="form-control text-center"
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
            step="0.01"
          />
        </div>
        <button class="submitPPORate" type="submit" :disabled="isSubmit">
          <span v-if="!isSubmit"> Submit</span>
          <span v-if="isSubmit">
            <i class="fa-solid fa-spinner"></i> Submiting</span
          >
        </button>
      </form>
    </div>
  </div>

  <div
    class="modal fade"
    id="ppoSuccessModal"
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
import { messaging, getToken, onMessage } from "@/firebase";

export default {
  data() {
    return {
      selectedRating: null,
      buttonVisible: true,
      formVisible: false,
      UserId: "",
      Month: "",
      Year: "",
      office: "",
      columns: [],
      formData: {},
      alertMessage: "",
      success: false,
      userMaxRate: 0,
      isSubmit: false,
      fcmToken: "",
      userName: "",
      level: "PPO / CPO Level",
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
    await this.requestPermission();
    await this.getMaxRateByUser();
  },

  methods: {
    async requestPermission() {
      const permission = await Notification.requestPermission();
      if (permission === "granted") {
        // console.log("Notification permission granted.");
        const registration = await navigator.serviceWorker.ready;
        if (registration) {
          // console.log("Service Worker is ready.");
          try {
            const currentToken = await getToken(messaging, {
              vapidKey:
                "BMl44ZV6cG8pDBXGieG0WYhRA0wKYSuiKC3xIR3hI2kxLJ4nfScWNZCu55G11dtYvQSiCSscFopIaRIPcG9rbWs",
              serviceWorkerRegistration: registration,
            });
            if (currentToken) {
              console.log("FCM Token:", currentToken);
              // this.fcmToken = currentToken;
            } else {
              console.log(
                "No registration token available. Request permission to generate one."
              );
            }
          } catch (err) {
            console.error("An error occurred while retrieving token. ", err);
          }
        } else {
          console.error("Service Worker is not ready.");
        }
      } else {
        console.log("Unable to get permission to notify.");
      }
    },

    async sentNotif() {
      try {
        const notifResponse = axios.post("/notifyAdmin", {
          UserName: this.userName,
          Office: this.office,
          Month: this.Month,
          Year: this.Year,
          Level: this.level,
        });
        if (notifResponse.status === 200) {
          alert("successfully send  notification");
        }
      } catch (e) {
        console.log(e);
      }
    },

    async getMaxRateByUser() {
      const userId = sessionStorage.getItem("id");
      try {
        const response = await axios.post("/getMaxRateByUser", {
          UserId: userId,
        });
        // console.log(response.data);
        this.userMaxRate = response.data.maxRate;
        this.userName = response.data.username;
        this.office = response.data.office;
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
        const response = await axios.get("/getColumnNamePPO");
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
        this.isSubmit = true;
        this.UserId = sessionStorage.getItem("id");
        const data = {
          UserId: this.UserId,
          Month: this.Month,
          Year: this.Year,
          ...this.formData, // Include formData properties
        };

        // Send data to server for insertion
        const response = await axios.post("/insertDataPPO", data);
        this.sentNotif();

        const modalElement = document.getElementById("ppoSuccessModal");
        const modalInstance = new Modal(modalElement);

        if (response.status === 200) {
          this.Month = "";
          this.Year = "";
          Object.keys(this.formData).forEach((key) => {
            this.formData[key] = "";
          });

          setTimeout(() => (this.isSubmit = false), 1000);

          this.alertMessage = "Successfully Rated";
          this.success = true;
          modalInstance.show();

          setTimeout(() => {
            modalInstance.hide();
            this.success = false;
          }, 5000);
        }
      } catch (error) {
        const modalElement = document.getElementById("ppoSuccessModal");
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

        setTimeout(() => (this.isSubmit = false), 1000);
        modalInstance.show();
        setTimeout(() => {
          modalInstance.hide();
        }, 5000);
      }
    },

    getPlaceholder(column) {
      return column.replace(/_/g, " ");
    },

    okayBtn() {
      const modalElement = document.getElementById("ppoSuccessModal");
      const modalInstance =
        Modal.getInstance(modalElement) || new Modal(modalElement);
      modalInstance.hide();

      // Manually remove the backdrop if it still exists
      const backdrop = document.querySelector(".modal-backdrop");
      if (backdrop) {
        backdrop.remove();
      }
    },
  },
};
</script>

<style>
.modalBg {
  height: 100vh;
  width: 100%;
  position: absolute;
  top: 0;
  left: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(70, 70, 70, 0.473);
}
.alertBox {
  height: 30%;
  width: 40%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  background-color: white;
  gap: 0.5rem;
  border-radius: 1rem;
}
.checkImg {
  height: 4rem;
}
.rating-header {
  display: flex;
  align-items: center;
  grid-gap: 16px;
  margin-bottom: 24px;
  justify-content: center;
}
.head-subtitle {
  text-align: center;
}
.ratingsheet-container {
  display: flex;
  align-items: center;
  flex-direction: column;
  gap: 0.8rem;
}
.rate-month,
.year-rate {
  border: 1px solid var(--light);
  padding: 0.2rem 0.5rem;
  color: var(--dark);
  background: var(--light);
  width: 16%;
}
.rateDate {
  /* width: 60%; */
  display: flex;
  justify-content: center;
  gap: 2rem;
  align-items: center;
}
.rateMonth,
.rateYear {
  padding: 0.3rem 0.5rem;
  border: 1px solid var(--light);
  border-radius: 0.5rem;
  color: var(--dark);
}
.rateInput {
  width: 60%;
  /* border: 1px solid var(--dark); */
  padding: 0.3rem 0.5rem;
  text-align: center;
  color: var(--dark);
  border-radius: 0.5rem;
  background: white;
}
.submitPPORate {
  background: green;
  padding: 0.5rem 1rem;
  color: white;
  border-radius: 0.5rem;
}

@media screen and (max-width: 600px) {
  .rateInput {
    width: 100%;
  }

  .rating-subheader {
    text-align: center;
  }
}
</style>
