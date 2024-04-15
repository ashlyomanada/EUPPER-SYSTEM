<template>
  <div class="table-data">
    <div class="order">
      <div class="head">
        <h3 class="">Request Form</h3>
      </div>
      <form class="form" @submit.prevent="submitForm">
        <input
          v-model="formData.username"
          type="text"
          placeholder="Username"
          class="input"
          required
        />
        <input
          v-model="formData.sender"
          type="text"
          placeholder="Email"
          class="input"
          required
        />
        <textarea
          v-model="formData.message"
          placeholder="Type message"
          required
        ></textarea>
        <button type="submit" :disabled="loading">
          <!-- Use a loader icon when loading is true -->
          <span v-if="loading">Requesting...</span>
          <span v-else>Submit Request</span>
        </button>
      </form>
    </div>
  </div>
  <div class="modalBg" v-if="showAlert">
    <div class="alertBox">
      <img class="checkImg" src="./img/check2.gif" alt="" />
      <h4 class="alertContent">{{ alertMessage }}</h4>
      <button class="btn btn-primary" @click="okayBtn">Okay</button>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      formData: {
        username: "",
        sender: "",
        message: "",
      },
      showAlert: false,
      alertMessage: "",
      loading: false, // Loading state to track form submission
    };
  },
  mounted() {
    this.fetchUserData(); // Fetch user data when the component is mounted
  },
  methods: {
    okayBtn() {
      this.showAlert = false;
    },
    submitForm() {
      // Disable form submission if already loading
      if (this.loading) return;

      // Set loading to true to show loader
      this.loading = true;

      // Send form data to server
      console.log("Form data:", this.formData);
      axios
        .post("/sendEmail", this.formData, {
          headers: {
            "Content-Type": "application/json",
          },
        })
        .then((response) => {
          console.log(response.data);
          this.formData = { username: "", sender: "", message: "" };
          this.showAlertMessage("Request Successfully Sent!");
        })
        .catch((error) => {
          console.error("Error sending email:", error);
          this.showAlertMessage("Error submitting the form. Please try again.");
        })
        .finally(() => {
          // Reset loading state after form submission completes
          this.loading = false;
        });
    },
    showAlertMessage(message) {
      this.showAlert = true;
      this.alertMessage = message;
      setTimeout(() => {
        this.showAlert = false;
        this.alertMessage = "";
      }, 5000);
    },
    async fetchUserData() {
      const storedUserId = sessionStorage.getItem("id");

      if (storedUserId) {
        try {
          const response = await axios.get(`/getUserData/${storedUserId}`);

          if (response.status === 200) {
            const userData = response.data;
            this.formData.username = userData.username;
            this.formData.sender = userData.email;
          } else {
            console.error(`Unexpected response status: ${response.status}`);
          }
        } catch (error) {
          console.error("Error fetching user data:", error);
        }
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
  border: 1px solid var(--dark);
  padding: 0.2rem 0.5rem;
  color: var(--dark);
  background: var(--light);
  width: 16%;
}
.rateDate {
  width: 60%;
  display: flex;
  justify-content: center;
  gap: 2rem;
  align-items: center;
}
.rateMonth,
.rateYear {
  padding: 0.3rem 0.5rem;
  border: 1px solid var(--dark);
  border-radius: 0.5rem;
}
.rateInput {
  width: 60%;
  border: 1px solid var(--dark);
  padding: 0.3rem 0.5rem;
  text-align: center;
  color: var(--dark);
  border-radius: 0.5rem;
}
.submitPPORate {
  background: green;
  padding: 0.5rem 1rem;
  color: white;
  border-radius: 0.5rem;
}
.form {
  position: relative;
  display: flex;
  align-items: flex-start;
  flex-direction: column;
  gap: 10px;
  width: 100%;
  background: var(--light);
  padding: 20px;
  border-radius: 10px;
}

.form input,
.form textarea {
  outline: 0;
  border: 1px solid rgb(219, 213, 213);
  padding: 8px 14px;
  border-radius: 8px;
  width: 100%;
  color: var(--dark);
}

.form textarea {
  border-radius: 8px;
  height: 100px;
  resize: none;
}

.form button {
  align-self: flex-end;
  padding: 8px;
  outline: 0;
  border: 0;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  background-color: royalblue;
  color: #fff;
  cursor: pointer;
}

.alert {
  margin-top: 10px;
  padding: 10px;
  border-radius: 8px;
  background-color: lightyellow;
  border: 1px solid #ccc;
}
</style>
