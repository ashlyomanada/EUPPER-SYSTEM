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
      <form class="form1" @submit.prevent="sendPasswordResetEmail">
        <p class="title">Request Reset Password</p>

        <label>
          <input required type="email" class="inputs" v-model="email" />
          <span>Email</span>
        </label>

        <button type="submit" class="submit text-white">
          Send Password Reset
        </button>
        <p class="signin">
          Already have an account? <router-link to="/">Sign in</router-link>
        </p>
        <p v-if="registrationStatus === 'error'" class="error-message">
          Invalid Email
        </p>
      </form>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { router } from "../router"; // Import Vue Router instance

export default {
  data() {
    return {
      email: "",
      registrationStatus: null,
    };
  },
  methods: {
    async sendPasswordResetEmail() {
      try {
        const response = await axios.post("/sendPasswordResetEmail", {
          email: this.email,
        });

        // Check if response status is successful
        if (response.status === 200) {
          // Reset registrationStatus to null
          this.registrationStatus = "success";
          // Show success message
          alert("Check your email for verification");
          // Redirect to reset password page with token
          const token = response.data.token;
          router.push({ name: "ResetPassword", params: { token } });
        }
      } catch (e) {
        console.log("Failed to send reset email:", e);
        this.registrationStatus = "error";
      }
    },
  },
};
</script>

<style>
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
