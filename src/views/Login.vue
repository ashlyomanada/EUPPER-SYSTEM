<template>
  <div class="register-container">
    <div class="register">
      <div class="picture-container">
        <div class="picture-container">
          <img src="./img/logo.png" class="loginLogo" alt="" />
          <div>
            <h1 class="login-title">EUPER SYSTEM</h1>
            <h2 class="title-name">PRO MIMAROPA</h2>
          </div>
        </div>
      </div>
      <form class="form1" @submit.prevent="login">
        <p class="title">Login</p>
        <p class="message">Sign in now and get full access to the app.</p>

        <label>
          <input required type="email" class="inputs" v-model="email" />
          <span>Email</span>
        </label>

        <label>
          <input required type="password" class="inputs" v-model="password" />
          <span>Password</span>
        </label>
        <button type="submit" class="submit">Login</button>
        <p class="signin">
          Don't have an account? <router-link to="/">Sign up</router-link>
        </p>
        <p v-if="loginError" class="error-message">{{ loginError }}</p>
      </form>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      email: "",
      password: "",
      loginError: null,
      isLoading: false,
    };
  },
  methods: {
    login() {
      const loginData = {
        email: this.email,
        password: this.password,
      };

      // Log loginData for debugging
      console.log("Login Data:", loginData);

      // Clear previous error message
      this.loginError = null;

      // Form validation
      if (!this.email || !this.password) {
        this.loginError = "Email and password are required";
        return;
      }

      this.isLoading = true;

      axios
        .post("/login", loginData)
        .then((response) => {
          if (response.data.success) {
            this.$router.replace("/home");
          } else {
            this.loginError = "Invalid email or password";
            console.error("Login failed:", response.data.message);
          }
        })
        .catch((error) => {
          this.loginError = "An error occurred during login. Please try again.";
          console.error("Login error:", error);
          // ...

          // Log additional details if available
          if (error.response) {
            console.error("Response status:", error.response.status);
            console.error("Response data:", error.response.data);
          } else if (error.request) {
            console.error(
              "No response received. Request details:",
              error.request
            );
          } else {
            console.error("Error details:", error.message);
          }
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
  },
};
</script>

<style>
.login-title {
  white-space: nowrap;
}
.title-name {
  text-align: center;
}

.error-message {
  color: red;
  margin-top: 10px;
}
</style>
