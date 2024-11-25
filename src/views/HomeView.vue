<template>
  <!-- SIDEBAR -->
  <section id="sidebar">
    <ul class="side-menu top" style="padding-left: 0">
      <div class="admin-logo2">
        <img src="./img/logo.png" alt="" id="logo2" />
        <h3 id="adminName2">{{ officeLocation }}</h3>
      </div>
      <li class="active">
        <a href="#" @click="showComponent('UserDashboard')">
          <i
            class="fa-solid fa-house"
            aria-hidden="true"
            style="padding: 0.5rem 0.8rem"
          ></i>
          <span class="text">Home</span>
        </a>
      </li>
      <li v-if="status === 'Enable'">
        <a href="#" id="btn" @click="toggleButtons">
          <div class="rate">
            <i
              class="fa fa-star"
              aria-hidden="true"
              style="padding: 0.5rem 0.8rem"
            ></i>
            <span class="text">Ratings Form</span>
          </div>
          <i v-if="showButtons" class="fa-solid fa-chevron-down"></i>
          <i v-else class="fa-solid fa-chevron-right"></i>
        </a>
      </li>
      <div v-show="showButtons" class="li-div">
        <li>
          <a href="#" @click="showComponent('PPORatingSheet')">
            <i
              class="fa fa-star"
              aria-hidden="true"
              style="padding: 0.5rem 0.8rem"
            ></i>
            <span class="text">PPO CPO Level</span>
          </a>
        </li>
        <li>
          <a href="#" @click="showComponent('RMFBRatingSheet')">
            <i
              class="fa fa-star"
              aria-hidden="true"
              style="padding: 0.5rem 0.8rem"
            ></i>
            <span class="text">RMFB PMFC Level</span>
          </a>
        </li>
        <li>
          <a href="#" @click="showComponent('MPSRatingSheet')">
            <i
              class="fa fa-star"
              aria-hidden="true"
              style="padding: 0.5rem 0.8rem"
            ></i>
            <span class="text">MPS CPS Level</span>
          </a>
        </li>
      </div>
      <li>
        <a href="#" id="btn2" @click="toggleButtons2">
          <div class="rate">
            <i
              class="fa fa-eye"
              aria-hidden="true"
              style="padding: 0.5rem 0.8rem"
            ></i>
            <span class="text">View Ratings</span>
          </div>
          <i v-if="showButtons2" class="fa-solid fa-chevron-down"></i>
          <i v-else class="fa-solid fa-chevron-right"></i>
        </a>
      </li>
      <div v-show="showButtons2" class="li-div">
        <li>
          <a href="#" @click="showComponent('UserPPORates')">
            <i
              class="fa fa-star"
              aria-hidden="true"
              style="padding: 0.5rem 0.8rem"
            ></i>
            <span class="text">PPO CPO Ratings</span>
          </a>
        </li>
        <li>
          <a href="#" @click="showComponent('UserRMFBRates')">
            <i
              class="fa fa-star"
              aria-hidden="true"
              style="padding: 0.5rem 0.8rem"
            ></i>
            <span class="text">RMFB PMFC Ratings</span>
          </a>
        </li>
        <li>
          <a href="#" @click="showComponent('UserMPSRates')">
            <i
              class="fa fa-star"
              aria-hidden="true"
              style="padding: 0.5rem 0.8rem"
            ></i>
            <span class="text">MPS CPS Ratings</span>
          </a>
        </li>
      </div>
      <li>
        <a href="#" id="userprof" @click="showComponent('UserProfile')">
          <i
            class="fa fa-user-circle"
            aria-hidden="true"
            style="padding: 0.5rem 0.8rem"
          ></i>
          <span class="text">User Profile</span>
        </a>
      </li>
      <li>
        <a href="#" @click="showComponent('RequestForm')">
          <i
            class="fa fa-envelope-open"
            aria-hidden="true"
            style="padding: 0.5rem 0.8rem"
          ></i>
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
        <a style="cursor: pointer" @click="confirmLogout" class="logout">
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
      <i class="bx bx-menu" @click="toggle"></i>
      <div class="w-50 d-flex">
        <a href="#" class="nav-link" style="color: var(--dark)"
          >PRO MIMAROPA E-UPER SYSTEM</a
        >
      </div>

      <div class="nav-items">
        <span class="time">{{ currentDateTime }}</span>
        <input type="checkbox" id="switch-mode" hidden />
        <label
          for="switch-mode"
          @click="toggleSwitch"
          class="switch-mode"
        ></label>
        <a
          href="#"
          class="profile"
          id="profile"
          @click="showComponent('UserProfile')"
        >
          <img :src="`${baseURL}${profilePic}`" />
        </a>
      </div>
    </nav>
    <!-- NAVBAR -->

    <!-- MAIN -->
    <main id="usermain">
      <UserDashboard v-if="selectedComponent === 'UserDashboard'" />
      <PPORatingSheet v-if="selectedComponent === 'PPORatingSheet'" />
      <Uper v-if="selectedComponent === 'Uper'" />
      <UserPPORates v-if="selectedComponent === 'UserPPORates'" />
      <UserRMFBRates v-if="selectedComponent === 'UserRMFBRates'" />
      <UserMPSRates v-if="selectedComponent === 'UserMPSRates'" />
      <RMFBRatingSheet v-if="selectedComponent === 'RMFBRatingSheet'" />
      <MPSRatingSheet v-if="selectedComponent === 'MPSRatingSheet'" />
      <ViewRatings v-if="selectedComponent === 'ViewRatings'" />
      <UserProfile v-if="selectedComponent === 'UserProfile'" />
      <RequestForm v-if="selectedComponent === 'RequestForm'" />
      <UserGmail v-if="selectedComponent === 'UserGmail'" />

      <!-- Bootstrap Modal -->
      <div
        class="modal fade"
        id="viewRatesModal"
        tabindex="-1"
        aria-labelledby="exampleModalLabel"
        aria-hidden="true"
      >
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel">
                View Rates Only
              </h5>
              <button
                type="button"
                class="btn-close"
                data-bs-dismiss="modal"
                aria-label="Close"
              ></button>
            </div>
            <div class="modal-body">You can only view rates at the moment.</div>
            <div class="modal-footer">
              <button
                type="button"
                class="btn btn-primary"
                data-bs-dismiss="modal"
                @click="okayBtn"
              >
                Okay
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>
    <!-- MAIN -->
  </section>

  <div
    class="modal fade"
    id="logout-modal2"
    tabindex="-1"
    aria-labelledby="successModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-body text-center">
          <h3 class="alertContent">Are you sure you want to log out?</h3>
        </div>
        <div class="modal-footer d-flex justify-content-center gap-3">
          <button type="button" class="btn btn-primary" @click="logout">
            Yes
          </button>
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
            No
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Uper from "../components/Uper.vue";
import ViewRatings from "../components/ViewRatings.vue";
import UserProfile from "../components/UserProfile.vue";
import RequestForm from "../components/RequestForm.vue";
import UserGmail from "../components/UserGmail.vue";
import UserPPORates from "../components/UserPPO.vue";
import UserRMFBRates from "../components/UserRMFB.vue";
import UserMPSRates from "../components/UserMPS.vue";
import UserDashboard from "../components/UserDashboard.vue";
import PPORatingSheet from "../components/ratingSheetForm/PPORatingSheet.vue";
import RMFBRatingSheet from "../components/ratingSheetForm/RMFBRatingSheet.vue";
import axios from "axios";
import router from "@/router";
import MPSRatingSheet from "../components/ratingSheetForm/MPSRatingSheet.vue";
import Vue from "vue";
import * as bootstrap from "bootstrap";
import { Modal } from "bootstrap";

export default {
  components: {
    Uper,
    ViewRatings,
    UserProfile,
    RequestForm,
    UserGmail,
    UserPPORates,
    UserRMFBRates,
    UserMPSRates,
    PPORatingSheet,
    RMFBRatingSheet,
    MPSRatingSheet,
    UserDashboard,
  },
  watch: {
    selectedComponent: function (newComponent) {
      document.title = "EUPER - " + newComponent;
    },
  },
  data() {
    return {
      selectedComponent: "UserDashboard",
      userId: null,
      userName: "",
      officeLocation: "",
      phoneNumber: "",
      email: "",
      profilePic: "",
      status: "",
      currentDateTime: "",
      dynamicallyLoadedScripts: [],
      baseURL: axios.defaults.baseURL,
      showButtons: false,
      showButtons2: false,
      showButtons3: false,
      userStatus: "",
    };
  },
  async created() {
    await this.loadData();
    this.toggle();
    if (this.status === "Disable") {
      this.$nextTick(() => {
        var myModal = new bootstrap.Modal(
          document.getElementById("viewRatesModal"),
          {
            keyboard: false,
          }
        );
        myModal.show();
      });
    }
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
            this.selectedComponent = "UserDashboard";
          }
        }
      } catch (error) {
        console.error("Error fetching user data:", error);
      }
    },
    confirmLogout() {
      const modalElement = document.getElementById("logout-modal2");
      const modalInstance = new Modal(modalElement);
      modalInstance.show();
    },
    async logout() {
      const modalElement = document.getElementById("logout-modal2");
      const modalInstance = new Modal(modalElement);
      const backdrop = document.querySelector(".modal-backdrop");
      if (backdrop) {
        backdrop.remove();
      }

      modalInstance.hide();
      if (sessionStorage.getItem("id") == null) {
        this.unloadScripts();
      }
      sessionStorage.removeItem("id");
      router.push("/");
      document.title = "EUPER";
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
      const head = document.getElementsByTagName("head")[0];
      this.dynamicallyLoadedScripts.forEach((script) => {
        head.removeChild(script);
      });
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
      this.showButtons2 = false;
    },
    toggleButtons2() {
      this.showButtons2 = !this.showButtons2;
      this.showButtons = false;
    },
    toggle() {
      const allSideMenu = document.querySelectorAll(
        "#sidebar .side-menu.top li a"
      );
      allSideMenu.forEach((item) => {
        const li = item.parentElement;
        item.addEventListener("click", function () {
          allSideMenu.forEach((i) => {
            i.parentElement.classList.remove("active");
          });
          li.classList.add("active");
        });
      });
      const menuBar = document.querySelector("#content nav .bx.bx-menu");
      const sidebar = document.getElementById("sidebar");
      const logo = document.getElementById("logo2");
      const adminName = document.getElementById("adminName2");
      menuBar.addEventListener("click", function () {
        sidebar.classList.toggle("hide");
        if (sidebar.classList.contains("hide")) {
          logo.style.height = "38px";
          adminName.style.visibility = "hidden";
        } else {
          logo.style.height = "120px";
          adminName.style.visibility = "unset";
        }
      });

      if (window.innerWidth < 768) {
        sidebar.classList.add("hide");
        logo.style.height = "38px";
        adminName.style.visibility = "hidden";
      }
    },

    toggleSwitch() {
      const switchMode = document.getElementById("switch-mode");
      const main = document.getElementById("usermain");
      switchMode.addEventListener("change", function () {
        if (this.checked) {
          document.body.classList.add("dark");
          main.style.backgroundColor = "#060714";
        } else {
          document.body.classList.remove("dark");
          main.style.backgroundColor = "#fbfbfb";
        }
      });
    },

    async checkUseStatus() {
      try {
        const id = sessionStorage.getItem("id");
        const response = await axios.get(`/checkUserStatus/${id}`);
        this.userStatus = response.data.role;

        if (id) {
          if (this.userStatus === "admin") {
            router.push("/");
            sessionStorage.removeItem("id");
          }
        }
      } catch (e) {
        console.log(e);
      }
    },
  },
  async mounted() {
    this.updateCurrentDateTime();
    setInterval(this.updateCurrentDateTime, 1000);

    await this.checkUseStatus();
  },
};
</script>

<style>
input,
select,
.btn,
textarea {
  box-shadow: rgba(0, 0, 0, 0.3) 0px 2px 4px,
    rgba(0, 0, 0, 0.2) 0px 7px 13px -3px, rgba(0, 0, 0, 0.1) 0px -3px 0px inset;
  border: var(--dark);
}
#content main .table-data .order {
  flex-grow: 1;
  flex-basis: 500px;
  box-shadow: rgba(0, 0, 0, 0.3) 0px 2px 4px,
    rgba(0, 0, 0, 0.2) 0px 7px 13px -3px, rgba(0, 0, 0, 0.1) 0px -3px 0px inset;
}
a {
  text-decoration: none;
}
.admin-logo2 {
  display: flex;
  align-items: center;
  flex-direction: column;
  gap: 1rem;
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
  align-items: center;
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
@media screen and (max-width: 1024px) {
  .nav-items {
    width: 60%;
  }
}
@media screen and (max-width: 768px) {
  .nav-items {
    width: 100%;
  }
}
@media screen and (max-width: 600px) {
  .nav-items {
    width: 60%;
    display: flex;
    justify-content: space-evenly;
    align-items: center;
  }

  .time {
    display: none;
  }
}
</style>
