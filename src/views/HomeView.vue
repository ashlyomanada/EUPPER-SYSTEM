<template>
  <!-- SIDEBAR -->
  <section id="sidebar">
    <ul class="side-menu top">
      <div class="admin-logo2">
        <img src="./img/logo.png" alt="" id="logo2" />
        <h1 id="adminName2">User</h1>
      </div>
      <li class="active">
        <a href="#" id="btn" @click="toggleButtons">
          <div class="rate">
            <i class="bx bxs-dashboard"></i>
            <span class="text">UPER</span>
          </div>
          <i v-if="showButtons" class="fa-solid fa-chevron-down"></i>
          <i v-else class="fa-solid fa-chevron-right"></i>
        </a>
      </li>
      <div v-show="showButtons" class="li-div">
        <li>
          <a href="#" @click="showComponent('UserPPO')">
            <i class="bx bxs-shopping-bag-alt"></i>
            <span class="text">PPO CPO Level</span>
          </a>
        </li>
        <li>
          <a href="#" @click="showComponent('UserRMFB')">
            <i class="bx bxs-shopping-bag-alt"></i>
            <span class="text">RMFB PMFC Level</span>
          </a>
        </li>
        <li>
          <a href="#" @click="showComponent('UserMPS')">
            <i class="bx bxs-shopping-bag-alt"></i>
            <span class="text">MPS CPS Level</span>
          </a>
        </li>
      </div>
      <li>
        <a href="#" id="btn2" @click="toggleButtons2">
          <div class="rate">
            <i class="bx bxs-shopping-bag-alt"></i>
            <span class="text">View Ratings</span>
          </div>
          <i v-if="showButtons2" class="fa-solid fa-chevron-down"></i>
          <i v-else class="fa-solid fa-chevron-right"></i>
        </a>
      </li>
      <div v-show="showButtons2" class="li-div">
        <li>
          <a href="#" @click="showComponent('UserPPO')">
            <i class="bx bxs-shopping-bag-alt"></i>
            <span class="text">PPO CPO Ratings</span>
          </a>
        </li>
        <li>
          <a href="#" @click="showComponent('UserRMFB')">
            <i class="bx bxs-shopping-bag-alt"></i>
            <span class="text">RMFB PMFC Ratings</span>
          </a>
        </li>
        <li>
          <a href="#" @click="showComponent('UserMPS')">
            <i class="bx bxs-shopping-bag-alt"></i>
            <span class="text">MPS CPS Ratings</span>
          </a>
        </li>
      </div>
      <li>
        <a href="#" id="userprof" @click="showComponent('UserProfile')">
          <i class="bx bxs-doughnut-chart"></i>
          <span class="text">User Profile</span>
        </a>
      </li>
      <li>
        <a href="#" @click="showComponent('RequestForm')">
          <i class="bx bxs-message-dots"></i>
          <span class="text">Request Form</span>
        </a>
      </li>
      <li>
        <a href="https://mail.google.com/" target="blank">
          <i class="bx bxs-group"></i>
          <span class="text">Your Gmail</span>
        </a>
      </li>
    </ul>
    <ul class="side-menu">
      <li>
        <router-link to="/login" class="logout">
          <i class="bx bxs-log-out-circle"></i>
          <span class="text">Logout</span>
        </router-link>
      </li>
    </ul>
  </section>
  <!-- SIDEBAR -->

  <!-- CONTENT -->
  <section id="content">
    <!-- NAVBAR -->
    <nav>
      <i class="bx bx-menu"></i>
      <a href="#" class="nav-link">PRO MIMAROPA E-UPER SYSTEM</a>
      <div class="nav-items">
        <input type="checkbox" id="switch-mode" hidden />
        <label for="switch-mode" class="switch-mode"></label>
        <a href="#" class="notification">
          <i class="bx bxs-bell"></i>
          <span class="num">8</span>
        </a>
        <a
          href="#"
          class="profile"
          id="profile"
          @click="showComponent('UserProfile')"
        >
          <img src="img/people.png" />
        </a>
      </div>
    </nav>
    <!-- NAVBAR -->

    <!-- MAIN -->
    <main id="usermain">
      <Uper v-if="selectedComponent === 'Uper'" />
      <UserPPO v-if="selectedComponent === 'UserPPO'" />
      <UserRMFB v-if="selectedComponent === 'UserRMFB'" />
      <UserMPS v-if="selectedComponent === 'UserMPS'" />
      <ViewRatings v-if="selectedComponent === 'ViewRatings'" />
      <UserProfile v-if="selectedComponent === 'UserProfile'" />
      <RequestForm v-if="selectedComponent === 'RequestForm'" />
      <UserGmail v-if="selectedComponent === 'UserGmail'" />
    </main>
    <!-- MAIN -->
  </section>
  <!-- CONTENT -->
</template>
<script>
import { defineComponent } from "vue";
import Uper from "../components/Uper.vue";
import ViewRatings from "../components/ViewRatings.vue";
import UserProfile from "../components/UserProfile.vue";
import RequestForm from "../components/RequestForm.vue";
import UserGmail from "../components/UserGmail.vue";
import UserPPO from "../components/UserPPO.vue";
import UserRMFB from "../components/UserRMFB.vue";
import UserMPS from "../components/UserMPS.vue";

// Components
export default defineComponent({
  components: {
    Uper,
    ViewRatings,
    UserProfile,
    RequestForm,
    UserGmail,
    UserPPO,
    UserRMFB,
    UserMPS,
  },
  data() {
    return {
      selectedComponent: "Uper",
      showButtons: false,
      showButtons2: false,
    };
  },
  async created() {
    await this.loadScripts(["/userscript.js"]);
  },
  methods: {
    showComponent(componentName) {
      this.selectedComponent = componentName;
    },
    toggleButtons() {
      this.showButtons = !this.showButtons;
    },
    toggleButtons2() {
      this.showButtons2 = !this.showButtons2;
    },
    loadScripts: function (scriptUrls) {
      // Changed arrow function to regular function
      const head = document.getElementsByTagName("head")[0];

      return Promise.all(
        scriptUrls.map(async (scriptUrl) => {
          const script = document.createElement("script");
          script.type = "text/javascript";
          script.src = scriptUrl;
          script.async = true;

          return new Promise((resolve, reject) => {
            script.onload = resolve;
            script.onerror = reject;
            head.appendChild(script);
          });
        })
      );
    },
  },
});
</script>
<style>
.admin-logo2 {
  display: flex;
  align-items: center;
  flex-direction: column;
  padding-bottom: 1rem;
}
#logo2 {
  height: 110px;
  background-color: white;
  border-radius: 50%;
}
#adminName2 {
  color: var(--dark);
}
.nav-items {
  width: 20%;
  display: flex;
  justify-content: space-evenly;
}
#btn,
#btn2 {
  justify-content: space-between;
}
.rate {
  display: flex;
  align-items: center;
}
.fa-solid {
  padding-right: 0.5rem;
}
.li-div {
  padding-left: 0.8rem;
}
@media screen and (max-width: 600px) {
  .nav-items {
    width: 60%;
    display: flex;
    justify-content: space-evenly;
  }
}
</style>
