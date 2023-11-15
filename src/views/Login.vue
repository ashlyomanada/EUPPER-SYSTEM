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
    };
  },
  methods: {
    login() {
      const loginData = {
        email: this.email,
        password: this.password,
      };
      axios
        .post("/login", loginData)
        .then((response) => {
          if (response.data.success) {
            this.$router.push("/home");
          } else {
            this.loginError = "Invalid email or password";
            console.error("Login failed");
          }
        })
        .catch((error) => {
          this.loginError = "An error occurred during login. Please try again.";
          console.error(error);
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
