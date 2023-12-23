<template>
  <div class="table-data">
    <div class="order">
      <div class="head">
        <h3>Request Form</h3>
      </div>
      <form class="form" @submit.prevent="submitForm">
        <input
          v-model="formData.username"
          type="text"
          placeholder="Username"
          class="input"
        />
        <input
          v-model="formData.sender"
          type="text"
          placeholder="Email"
          class="input"
        />
        <textarea
          v-model="formData.message"
          placeholder="Type message"
        ></textarea>
        <button type="submit">Submit Request</button>
      </form>
      <div v-if="showAlert" class="alert">
        {{ alertMessage }}
      </div>
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
      userId: null,
      userName: "",
      profilePic: "",
      officeLocation: "",
      phoneNumber: "",
      email: "",
    };
  },
  methods: {
    submitForm() {
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
          this.formData = {
            username: "",
            sender: "",
            message: "",
          };
          this.showAlertMessage("Form submitted successfully!");
        })
        .catch((error) => {
          console.error("Error sending email:", error);
          this.showAlertMessage("Error submitting the form. Please try again.");
        });
    },
    showAlertMessage(message) {
      this.showAlert = true;
      this.alertMessage = message;

      // Hide the alert after a certain duration (e.g., 5 seconds)
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
            this.userId = userData.user_id;
            this.userName = userData.username;
            this.officeLocation = userData.office;
            this.phoneNumber = userData.phone_no;
            this.email = userData.email;
            this.profilePic = userData.image;
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

.form .title {
  color: royalblue;
  font-size: 30px;
  font-weight: 600;
  letter-spacing: -1px;
  line-height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.form input {
  outline: 0;
  border: 1px solid rgb(219, 213, 213);
  padding: 8px 14px;
  border-radius: 8px;
  width: 100%;
  height: 50px;
  color: var(--dark);
}

.form textarea {
  border-radius: 8px;
  height: 100px;
  width: 100%;
  resize: none;
  outline: 0;
  padding: 8px 14px;
  border: 1px solid rgb(219, 213, 213);
  color: var(--dark);
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
@media screen and (max-width: 600px) {
  .form {
    width: 100vw;
  }
}
</style>
