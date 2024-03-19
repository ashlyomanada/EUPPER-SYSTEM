<template>
  <!-- SIDEBAR -->
  <section id="sidebar">
    <ul class="side-menu top" style="padding-left: 0">
      <div class="admin-logo">
        <img src="./img/logo.png" alt="" id="logo" />
        <h2 id="adminName">{{ userName }}</h2>
      </div>
      <li class="active">
        <a href="#" @click="showComponent('Dashboard')">
          <i class="bx bxs-dashboard"></i>
          <span class="text">Dashboard</span>
        </a>
      </li>
      <li>
        <a href="#" id="rate-btn" @click="toggleButtons">
          <div class="rate">
            <i class="bx bxs-shopping-bag-alt"></i>
            <span class="text">Ratings</span>
          </div>
          <i v-if="showButtons" class="fa-solid fa-chevron-down"></i>
          <i v-else class="fa-solid fa-chevron-right"></i>
        </a>
      </li>
      <div v-show="showButtons" class="li-div">
        <li>
          <a href="#" @click="showComponent('PPO')">
            <i class="bx bxs-shopping-bag-alt"></i>
            <span class="text">PPO CPO Ratings</span>
          </a>
        </li>
        <li>
          <a href="#" @click="showComponent('RMFB')">
            <i class="bx bxs-shopping-bag-alt"></i>
            <span class="text">RMFB PMFC Ratings</span>
          </a>
        </li>
        <li>
          <a href="#" @click="showComponent('AdminMPS')">
            <i class="bx bxs-shopping-bag-alt"></i>
            <span class="text">MPS CPS Ratings</span>
          </a>
        </li>
      </div>
      <li>
        <a href="#" id="rate-btn2" @click="toggleButtons2">
          <div class="rate">
            <i class="bx bxs-group"></i>
            <span class="text">Manage User</span>
          </div>
          <i v-if="showButtons2" class="fa-solid fa-chevron-down"></i>
          <i v-else class="fa-solid fa-chevron-right"></i>
        </a>
      </li>
      <div v-show="showButtons2" class="li-div">
        <li>
          <a href="#" @click="showComponent('ManageUser')">
            <i class="bx bxs-group"></i>
            <span class="text">Users</span>
          </a>
        </li>
        <li>
          <a href="#" @click="showComponent('Ratings')">
            <i class="bx bxs-group"></i>
            <span class="text">User Ratings</span>
          </a>
        </li>
        <li>
          <a href="#" @click="showComponent('AdminGmail')">
            <i class="bx bxs-shopping-bag-alt"></i>
            <span class="text">Announcement</span>
          </a>
        </li>
      </div>
      <li>
        <a href="#" @click="showComponent('ManageAdmin')">
          <i class="bx bxs-message-dots"></i>
          <span class="text">Manage Admin</span>
        </a>
      </li>
      <li>
        <a href="https://mail.google.com/" target="blank">
          <i class="bx bxs-group"></i>
          <span class="text">Your Gmail</span>
        </a>
      </li>
    </ul>
    <ul class="side-menu" style="padding-left: 0">
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
    <Navbar />
    <!-- NAVBAR -->

    <!-- MAIN -->
    <main id="main">
      <Dashboard v-if="selectedComponent === 'Dashboard'" />
      <Ratings v-if="selectedComponent === 'Ratings'" />
      <PPO v-if="selectedComponent === 'PPO'" />
      <RMFB v-if="selectedComponent === 'RMFB'" />
      <AdminMPS v-if="selectedComponent === 'AdminMPS'" />
      <ManageUser v-if="selectedComponent === 'ManageUser'" />
      <ManageAdmin v-if="selectedComponent === 'ManageAdmin'" />
      <AdminGmail v-if="selectedComponent === 'AdminGmail'" />
    </main>
    <!-- MAIN -->
  </section>
  <!-- CONTENT -->
</template>

<script>
import Navbar from "../components/Navbar.vue";
import Dashboard from "../components/Dashboard.vue";
import Ratings from "../components/Ratings.vue";
import ManageUser from "../components/ManageUser.vue";
import ManageAdmin from "../components/ManageAdmin.vue";
import AdminGmail from "../components/AdminGmail.vue";
import PPO from "../components/PPO.vue";
import RMFB from "../components/RMFB.vue";
import AdminMPS from "../components/AdminMPS.vue";
import axios from "axios";

export default {
  components: {
    Navbar,
    Dashboard,
    Ratings,
    ManageUser,
    ManageAdmin,
    AdminGmail,
    PPO,
    RMFB,
    AdminMPS,
  },
  data() {
    return {
      selectedComponent: "Dashboard",
      showButtons: false,
      showButtons2: false,
      userName: "",
    };
  },
  async created() {
    await this.loadScripts(["/script.js"]);
  },
  mounted() {
    this.fetchUserData();
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

    async fetchUserData() {
      const storedUserId = sessionStorage.getItem("id");
      if (storedUserId) {
        try {
          const response = await axios.get(`/getUserData/${storedUserId}`);
          if (response.status === 200) {
            const userData = response.data;
            this.userName = userData.username;
            console.log(this.userName);
          } else {
            console.error(`Unexpected response status: ${response.status}`);
          }
        } catch (e) {
          console.log(e);
        }
      }
    },
  },
};
</script>
<style>
a {
  text-decoration: none;
}
#main {
  position: relative;
}
.admin-logo {
  display: flex;
  align-items: center;
  flex-direction: column;
  padding-bottom: 1rem;
}
#logo {
  height: 110px;
  background-color: white;
  border-radius: 50%;
}
#adminName {
  color: var(--dark);
}
#rate-btn,
#rate-btn2 {
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
</style>
