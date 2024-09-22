<template>
  <v-app>
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
          <button type="submit" class="submit" style="color: white">
            Login
          </button>
          <router-link to="/forgot" class="signin"
            >Forgot Password?</router-link
          >
        </form>
      </div>

      <div class="alert-container">
        <v-alert v-if="errorMessage" type="error" class="error-message">{{
          errorMessage
        }}</v-alert>
      </div>
    </div>
  </v-app>
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

  mounted() {
    setInterval(() => {
      this.errorMessage = null;
    }, 5000);
  },

  methods: {
    async login() {
      const data = {
        email: this.email,
        password: this.password,
      };

      try {
        const response = await axios.post("/login", JSON.stringify(data), {
          headers: {
            "Content-Type": "application/json",
          },
        });

        if (response.data.message === "Login successful") {
          const role = response.data.role;
          sessionStorage.setItem("token", response.data.token);
          sessionStorage.setItem("id", response.data.id);
          router.push(role === "user" ? "/home" : "/adminHome");
        }
      } catch (error) {
        // console.error(error);
        this.email = "";
        this.password = "";
        this.errorMessage = "Invalid email or password, try again!";
      }
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
  margin-top: 10px;
}
.alert-container {
  position: absolute;
  top: 0;
  right: 0;
  z-index: 100;
}
</style>
-
