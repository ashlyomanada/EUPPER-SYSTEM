<template>
  <!-- SIDEBAR -->
  <section id="sidebar">
    <ul class="side-menu top" style="padding-left: 0">
      <div class="admin-logo2">
        <img src="./img/logo.png" alt="" id="logo2" />
        <h3 id="adminName2">{{ userName }}</h3>
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
        <router-link to="/" class="logout">
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
    PPORatingSheet,
    RMFB,
    MPSRatingSheet,
    UserMPS,
  },
  data() {
    return {
      selectedComponent: "PPORatingSheet",
      showButtons: false,
      showButtons2: false,
      userId: null,
      userName: "",
      officeLocation: "",
      phoneNumber: "",
      email: "",
      profilePic: "",
      status: "",
    };
  },
  async created() {
    await this.loadScripts(["/userscript.js"]);
  },
  mounted() {
    // Retrieve user information from session storage
    const storedUserId = sessionStorage.getItem("id");

    // Check if the user is logged in
    if (storedUserId) {
      // Make an Axios request to fetch user data based on session ID
      axios
        .get(`/getUserData/${storedUserId}`)
        .then((response) => {
          // Update the component's data with the fetched user data
          const userData = response.data;
          this.userId = userData.id;
          this.userName = userData.username;
          this.officeLocation = userData.office;
          this.phoneNumber = userData.phone_no;
          this.email = userData.email;
          this.profilePic = userData.image;
          this.status = userData.status;

          if (this.status === "Disable") {
            this.selectedComponent = "UserPPO";
          }
          console.log(userData);
        })
        .catch((error) => {
          console.error("Error fetching user data:", error);
        });
    }
  },
  methods: {
    logout() {
      router.push("/login");
    },
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
