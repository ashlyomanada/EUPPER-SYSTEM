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
        <button type="submit" class="submit" style="color: white">Login</button>
        <p class="signin">
          Don't have an account?
          <router-link to="/register">Sign up</router-link>
        </p>
        <p class="error-message">{{ errorMessage }}</p>
      </form>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import router from "@/router";
export default {
  data() {
    return {
      email: "",
      password: "",
      errorMessage: "",
    };
  },
  methods: {
    login() {
      const data = {
        email: this.email,
        password: this.password,
      };

      axios
        .post("/login", JSON.stringify(data), {
          headers: {
            "Content-Type": "application/json",
          },
        })
        .then((response) => {
          if (response.data.message === "Login successful") {
            const role = response.data.role;
            sessionStorage.setItem("token", response.data.token);
            sessionStorage.setItem("id", response.data.id);
            router.push(role === "user" ? "/home" : "/admin");
          }
        })

        .catch((error) => {
          console.error(error);
          this.errorMessage = "Invalid email or password, try again!";
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
