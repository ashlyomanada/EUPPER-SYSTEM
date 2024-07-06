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
          readonly
        />

        <input
          v-model="formData.sender"
          type="text"
          placeholder="Email"
          class="input"
          required
          readonly
        />

        <div class="d-flex gap-1">
          <input
            v-if="showButton1"
            type="button"
            value="I need to request enabling a form in the system."
            @click="
              addToTextarea(
                'I need to request enabling a form in the system.',
                1
              )
            "
            readonly
          />
          <input
            v-if="showButton2"
            type="button"
            value="Could you please enable the form for me?"
            @click="
              addToTextarea('Could you please enable the form for me?', 2)
            "
            readonly
          />
        </div>

        <textarea
          v-model="formData.message"
          placeholder="Type message"
          required
        ></textarea>

        <button type="submit" :disabled="loading">
          <span v-if="loading">Requesting...</span>
          <span v-else>Submit Request</span>
        </button>
      </form>
    </div>
    <div class="alert-container">
      <v-alert v-if="errors.username" type="error" class="error">{{
        errors.username
      }}</v-alert>
    </div>
    <div class="alert-container">
      <v-alert v-if="errors.sender" type="error" class="error">{{
        errors.sender
      }}</v-alert>
    </div>
    <div class="alert-container">
      <v-alert v-if="errors.message" type="error" class="error">{{
        errors.message
      }}</v-alert>
    </div>
  </div>

  <!-- Bootstrap Modal -->
  <div
    class="modal fade"
    id="alertModal"
    tabindex="-1"
    aria-labelledby="alertModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body text-center">
          <img class="checkImg" src="./img/check2.gif" alt="" />
          <h4 class="alertContent">{{ alertMessage }}</h4>
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-primary"
            @click="okayBtn"
            data-bs-dismiss="modal"
          >
            Okay
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import * as bootstrap from "bootstrap";

export default {
  data() {
    return {
      formData: {
        username: "",
        sender: "",
        message: "",
      },
      errors: {},
      showAlert: false,
      alertMessage: "",
      loading: false,
      showButton1: true,
      showButton2: true,
    };
  },
  mounted() {
    this.fetchUserData();
    setInterval(() => {
      this.errors = {};
    }, 5000);
  },
  methods: {
    okayBtn() {
      this.showAlert = false;
      const modal = document.getElementById("alertModal");
      const bootstrapModal = bootstrap.Modal.getInstance(modal);
      if (bootstrapModal) bootstrapModal.hide();
    },
    validateForm() {
      this.errors = {};
      let valid = true;

      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!this.formData.sender || !emailPattern.test(this.formData.sender)) {
        this.errors.sender = "Please enter a valid email address.";
        valid = false;
      }

      if (!this.formData.message || this.formData.message.length < 5) {
        this.errors.message = "Message must be at least 5 characters long.";
        valid = false;
      }

      return valid;
    },
    submitForm() {
      if (this.loading) return;
      if (!this.validateForm()) return;

      this.loading = true;

      axios
        .post("/sendEmail", this.formData, {
          headers: {
            "Content-Type": "application/json",
          },
        })
        .then((response) => {
          console.log(response.data);
          this.formData.message = "";
          this.showAlertMessage("Request Successfully Sent!");
        })
        .catch((error) => {
          console.error("Error sending email:", error);
          this.showAlertMessage("Error submitting the form. Please try again.");
        })
        .finally(() => {
          this.loading = false;
        });
    },
    showAlertMessage(message) {
      this.showAlert = true;
      this.alertMessage = message;
      const modal = new bootstrap.Modal(document.getElementById("alertModal"));
      modal.show();
      setTimeout(() => {
        this.okayBtn();
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
    addToTextarea(value, buttonNumber) {
      this.formData.message = value;
      if (buttonNumber === 1) {
        this.showButton1 = false;
      } else if (buttonNumber === 2) {
        this.showButton2 = false;
      }
    },
  },
};
</script>

<style>
.alert-container {
  position: absolute;
  top: 0;
  right: 0;
  z-index: 100;
}
.checkImg {
  height: 4rem;
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

.d-flex {
  display: flex;
}

.gap-1 {
  gap: 10px;
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

.modal-body {
  text-align: center;
}

.error {
  color: red;
  font-size: 0.875em;
}
</style>
