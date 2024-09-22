<template>
  <div class="register-container">
    <div class="register">
      <div class="picture-container">
        <img src="./img/logo.png" class="loginLogo" alt="" />
        <div class="name-container">
          <h1>EUPER SYSTEM</h1>
          <h2>PRO MIMAROPA</h2>
        </div>
      </div>
      <form
        class="form1"
        @submit.prevent="sendPasswordResetEmail"
        :class="{ processing: processing }"
      >
        <p class="title">Request Reset Password</p>

        <label>
          <input required type="email" class="inputs" v-model="email" />
          <span>Email</span>
        </label>

        <button type="submit" class="submit text-white" :disabled="processing">
          <span v-if="!processing">Send Password Reset</span>
          <span v-else>Sending...</span>
        </button>
        <p class="signin">
          Already have an account? <router-link to="/">Sign in</router-link>
        </p>
      </form>
    </div>

    <div class="alert-container">
      <v-alert v-if="errorMessage" type="error" class="error-message">{{
        errorMessage
      }}</v-alert>
    </div>

    <div class="alert-container">
      <v-alert v-if="successMessage" type="success" class="error-message">{{
        successMessage
      }}</v-alert>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { useRouter } from "vue-router"; // Import Vue Router instance

export default {
  data() {
    return {
      email: "",
      errorMessage: "",
      successMessage: "",
      registrationStatus: null,
      processing: false, // Flag to track form processing state
    };
  },
  setup() {
    const router = useRouter(); // Correctly use Vue Router's useRouter hook
    return { router };
  },
  methods: {
    async sendPasswordResetEmail() {
      if (this.processing) return; // Prevent duplicate requests

      this.processing = true; // Set processing flag to true at the start
      try {
        const response = await axios.post("/sendPasswordResetEmail", {
          email: this.email,
        });

        if (response.status === 200) {
          this.registrationStatus = "success";

          this.successMessage = "Check your email for verification.";
          this.email = ""; // Clear email only after success
          setTimeout(() => {
            this.successMessage = null;
          }, 5000);
        } else {
          this.registrationStatus = "error";
          this.errorMessage = "An unexpected error occurred. Please try again.";
        }
      } catch (error) {
        if (error.response && error.response.status === 404) {
          this.errorMessage = "The email you entered doesn't exist.";
        } else if (error.response && error.response.status === 500) {
          this.errorMessage = "Server error. Please try again later.";
        } else {
          this.errorMessage =
            "Failed to send the password reset email. Check your connection.";
        }
        // console.error(error); // Log the error for debugging

        setTimeout(() => {
          this.errorMessage = null;
        }, 5000);
      } finally {
        this.processing = false; // Always reset processing flag in `finally`
      }
    },
  },
};
</script>

<style>
/* Add style for the processing state */
.form1.processing button:disabled {
  opacity: 0.7; /* Reduce opacity when button is disabled during processing */
  cursor: not-allowed; /* Change cursor to not-allowed */
}
.row {
  display: flex;
  gap: 0.5rem;
}
.column {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  width: 50%;
}
.register-container {
  height: 100vh;
  width: 100vw;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #0c0c1e;
}
.register {
  height: 85vh;
  width: 65vw;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #fff;
  border-radius: 1.5rem;
  box-shadow: 0 30px 30px -30px rgba(27, 26, 26, 0.315);
}
.picture-container {
  height: 100%;
  width: 50%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 3rem;
}
.name-container {
  text-align: center;
}
.loginLogo {
  height: 200px;
}
.form1 {
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-width: 500px;
  background-color: #fff;
  padding: 20px;
  border-radius: 20px;
  position: relative;
  width: 70vw;
}

.title {
  font-size: 28px;
  color: royalblue;
  font-weight: 600;
  letter-spacing: -1px;
  position: relative;
  display: flex;
  align-items: center;
  padding-left: 30px;
}

.title::before,
.title::after {
  position: absolute;
  content: "";
  height: 16px;
  width: 16px;
  border-radius: 50%;
  left: 0px;
  background-color: royalblue;
}

.title::before {
  width: 18px;
  height: 18px;
  background-color: royalblue;
}

.title::after {
  width: 18px;
  height: 18px;
  animation: pulse 1s linear infinite;
}

.message,
.signin {
  color: black;
  font-size: 14px;
}

.signin {
  text-align: center;
}

.signin a {
  color: royalblue;
}

.signin a:hover {
  text-decoration: underline royalblue;
}

.flex {
  display: flex;
  width: 100%;
  gap: 6px;
}

.form1 label {
  position: relative;
}

.form1 label .inputs {
  width: 100%;
  padding: 10px 10px 20px 10px;
  outline: 0;
  border: 1px solid black;
  border-radius: 10px;
}

.form1 label .inputs + span {
  position: absolute;
  left: 10px;
  top: 15px;
  color: black;
  font-size: 0.9em;
  cursor: text;
  transition: 0.3s ease;
}

.form1 label .inputs:placeholder-shown + span {
  top: 15px;
  font-size: 0.9em;
}

.form1 label .inputs:focus + span,
.form1 label .inputs:valid + span {
  top: 30px;
  font-size: 0.7em;
  font-weight: 600;
}

.form1 label .inputs:valid + span {
  color: green;
}

.submit {
  border: none;
  outline: none;
  background-color: royalblue;
  padding: 10px;
  border-radius: 10px;
  color: #fff;
  font-size: 16px;
  transform: 0.3s ease;
}

.submit:hover {
  background-color: rgb(56, 90, 194);
}

@keyframes pulse {
  from {
    transform: scale(0.9);
    opacity: 1;
  }

  to {
    transform: scale(1.8);
    opacity: 0;
  }
}
@media screen and (max-width: 768px) {
  .register {
    width: 90vw;
  }
}
@media screen and (max-width: 600px) {
  .picture-container {
    display: none;
  }
  .form1 {
    width: 100vw;
  }
  .register {
    background-color: transparent;
  }
}
</style>
