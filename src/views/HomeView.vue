<template>
  <!-- SIDEBAR -->
  <section id="sidebar">
    <ul class="side-menu top" style="padding-left: 0">
      <div class="admin-logo2">
        <img src="./img/logo.png" alt="" id="logo2" />
        <h3 id="adminName2">{{ officeLocation }}</h3>
      </div>
      <li class="active" v-if="status === 'Enable'">
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
          <a href="#" @click="showComponent('PPORatingSheet')">
            <i class="bx bxs-shopping-bag-alt"></i>
            <span class="text">PPO CPO Level</span>
          </a>
        </li>
        <li>
          <a href="#" @click="showComponent('RMFB')">
            <i class="bx bxs-shopping-bag-alt"></i>
            <span class="text">RMFB PMFC Level</span>
          </a>
        </li>
        <li>
          <a href="#" @click="showComponent('MPSRatingSheet')">
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
    <ul class="side-menu" style="padding-left: 0">
      <li>
        <a @click="logout" class="logout">
          <i class="bx bxs-log-out-circle"></i>
          <span class="text">Logout</span>
        </a>
      </li>
    </ul>
  </section>
  <!-- SIDEBAR -->

  <!-- CONTENT -->
  <section id="content">
    <!-- NAVBAR -->
    <nav>
      <i class="bx bx-menu"></i>
      <div class="w-50 d-flex">
        <a href="#" class="nav-link">PRO MIMAROPA E-UPER SYSTEM</a>
      </div>

      <div class="nav-items">
        <span class="time">{{ currentDateTime }}</span>
        <input type="checkbox" id="switch-mode" hidden />
        <label for="switch-mode" class="switch-mode"></label>
        <a
          href="#"
          class="profile"
          id="profile"
          @click="showComponent('UserProfile')"
        >
          <img :src="`http://localhost:8080/${profilePic}`" />
        </a>
      </div>
    </nav>
    <!-- NAVBAR -->

    <!-- MAIN -->
    <main id="usermain">
      <PPORatingSheet v-if="selectedComponent === 'PPORatingSheet'" />
      <Uper v-if="selectedComponent === 'Uper'" />
      <UserPPO v-if="selectedComponent === 'UserPPO'" />
      <UserRMFB v-if="selectedComponent === 'UserRMFB'" />
      <RMFB v-if="selectedComponent === 'RMFB'" />
      <UserMPS v-if="selectedComponent === 'UserMPS'" />
      <MPSRatingSheet v-if="selectedComponent === 'MPSRatingSheet'" />
      <ViewRatings v-if="selectedComponent === 'ViewRatings'" />
      <UserProfile v-if="selectedComponent === 'UserProfile'" />
      <RequestForm v-if="selectedComponent === 'RequestForm'" />
      <UserGmail v-if="selectedComponent === 'UserGmail'" />

      <div class="modalBg" v-if="status === 'Disable'">
        <div class="alertBox">
          <!-- <img class="checkImg" src="./img/check2.gif" alt="" /> -->
          <h3 class="alertContent">View Rates Only</h3>
          <button class="btn btn-primary" @click="okayBtn">Okay</button>
        </div>
      </div>
    </main>
    <!-- MAIN -->
  </section>
  <!-- CONTENT -->
</template>
<script>
import Uper from "../components/Uper.vue";
import ViewRatings from "../components/ViewRatings.vue";
import UserProfile from "../components/UserProfile.vue";
import RequestForm from "../components/RequestForm.vue";
import UserGmail from "../components/UserGmail.vue";
import UserPPO from "../components/UserPPO.vue";
import UserRMFB from "../components/UserRMFB.vue";
import UserMPS from "../components/UserMPS.vue";
import PPORatingSheet from "../components/ratingSheetForm/PPORatingSheet.vue";
import RMFB from "../components/ratingSheetForm/RMFBRatingSheet.vue";
import axios from "axios";
import router from "@/router";
import MPSRatingSheet from "../components/ratingSheetForm/MPSRatingSheet.vue";
// Components
export default {
  components: {
    Uper,
    ViewRatings,
    UserProfile,
    RequestForm,
    UserGmail,
    UserPPO,
    UserRMFB,
    UserMPS,
    PPORatingSheet,
    RMFB,
    MPSRatingSheet,
  },
  data() {
    return {
      selectedComponent: "PPORatingSheet",
      userId: null,
      userName: "",
      officeLocation: "",
      phoneNumber: "",
      email: "",
      profilePic: "",
      status: "",
      currentDateTime: "", // To store current date and time
      dynamicallyLoadedScripts: [], // Array to store loaded script elements
    };
  },
  async created() {
    await this.loadScripts(["/userscript.js"]);
    await this.loadData();
  },
  methods: {
    async loadData() {
      try {
        const storedUserId = sessionStorage.getItem("id");
        if (storedUserId) {
          const response = await axios.get(`/getUserData/${storedUserId}`);
          const userData = response.data;
          this.officeLocation = userData.office;
          this.profilePic = userData.image;
          this.status = userData.status;
          if (this.status === "Disable") {
            this.selectedComponent = "UserPPO";
          }
        }
      } catch (error) {
        console.error("Error fetching user data:", error);
      }
    },
    async logout() {
      this.unloadScripts();
      sessionStorage.removeItem("id");
      router.push("/");
    },
    okayBtn() {
      this.status = "Closed";
    },
    updateCurrentDateTime() {
      const currentDate = new Date();
      const options = {
        month: "long",
        day: "numeric",
        year: "numeric",
        hour: "numeric",
        minute: "numeric",
        second: "numeric",
      };
      this.currentDateTime = currentDate.toLocaleDateString("en-US", options);
    },

    showComponent(componentName) {
      this.selectedComponent = componentName;
    },
    unloadScripts() {
      // Remove all dynamically loaded script elements from the DOM
      const head = document.getElementsByTagName("head")[0];
      this.dynamicallyLoadedScripts.forEach((script) => {
        head.removeChild(script);
      });
      // Clear the array tracking loaded script elements
      this.dynamicallyLoadedScripts = [];
    },
    async loadScripts(scriptUrls) {
      const head = document.getElementsByTagName("head")[0];
      await Promise.all(
        scriptUrls.map((scriptUrl) => {
          return new Promise((resolve, reject) => {
            const script = document.createElement("script");
            script.type = "text/javascript";
            script.src = scriptUrl;
            script.async = true;
            script.onload = () => {
              this.dynamicallyLoadedScripts.push(script);
              resolve();
            };
            script.onerror = reject;
            head.appendChild(script);
          });
        })
      );
    },

    toggleButtons() {
      this.showButtons = !this.showButtons;
    },
    toggleButtons2() {
      this.showButtons2 = !this.showButtons2;
    },
  },
  // Call updateCurrentDateTime() once the component is mounted
  mounted() {
    this.updateCurrentDateTime();
  },
};
</script>
<style>
a {
  text-decoration: none;
}
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
#adminName2,
.time {
  color: var(--dark);
}
.nav-items {
  width: 40%;
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
