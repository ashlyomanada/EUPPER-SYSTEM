<template>
  <!-- SIDEBAR -->
  <section id="sidebar" class="sideBar">
    <ul class="side-menu top" style="padding-left: 0">
      <div class="admin-logo">
        <img src="./img/logo.png" alt="" id="logo" />
        <h3 id="adminName" class="text-center">
          {{ userData.username }}
        </h3>
      </div>
      <li :class="{ active: selectedComponent === 'Dashboard' }">
        <a href="#" @click="showComponent('Dashboard')">
          <i class="bx bxs-dashboard"></i>
          <span class="text">Dashboard</span>
        </a>
      </li>
      <li>
        <a href="#" id="rate-btn" @click="toggleButtons">
          <div class="rate">
            <i
              class="fa fa-star"
              aria-hidden="true"
              style="padding: 0.5rem 0.8rem"
            ></i>
            <span class="text">Ratings</span>
          </div>
          <i v-if="showButtons" class="fa-solid fa-chevron-down"></i>
          <i v-else class="fa-solid fa-chevron-right"></i>
        </a>
      </li>
      <div v-show="showButtons" class="li-div">
        <li :class="{ active: selectedComponent === 'PPORates' }">
          <a href="#" @click="showComponent('PPORates')">
            <i
              class="fa fa-star"
              aria-hidden="true"
              style="padding: 0.5rem 0.8rem"
            ></i>
            <span class="text">PPO CPO Ratings</span>
          </a>
        </li>
        <li :class="{ active: selectedComponent === 'RMFBRates' }">
          <a href="#" @click="showComponent('RMFBRates')">
            <i
              class="fa fa-star"
              aria-hidden="true"
              style="padding: 0.5rem 0.8rem"
            ></i>
            <span class="text">RMFB PMFC Ratings</span>
          </a>
        </li>
        <li :class="{ active: selectedComponent === 'MPSRates' }">
          <a href="#" @click="showComponent('MPSRates')">
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
        <a href="#" id="rate-btn2" @click="toggleButtons2">
          <div class="rate">
            <i
              class="fa fa-user"
              aria-hidden="true"
              style="padding: 0.5rem 0.8rem"
            ></i>
            <span class="text">Manage User</span>
          </div>
          <i v-if="showButtons2" class="fa-solid fa-chevron-down"></i>
          <i v-else class="fa-solid fa-chevron-right"></i>
        </a>
      </li>
      <div v-show="showButtons2" class="li-div">
        <li :class="{ active: selectedComponent === 'ManageUser' }">
          <a href="#" @click="showComponent('ManageUser')">
            <i class="bx bxs-group"></i>
            <span class="text">Users</span>
          </a>
        </li>
        <li :class="{ active: selectedComponent === 'Ratings' }">
          <a href="#" @click="showComponent('Ratings')">
            <i
              class="fa fa-star"
              aria-hidden="true"
              style="padding: 0.5rem 0.8rem"
            ></i>
            <span class="text">User Ratings</span>
          </a>
        </li>
        <li :class="{ active: selectedComponent === 'ManageRate' }">
          <a href="#" @click="showComponent('ManageRate')">
            <i
              class="fa-solid fa-gear"
              aria-hidden="true"
              style="padding: 0.5rem 0.8rem"
            ></i>
            <span class="text">Manage Max Rate</span>
          </a>
        </li>
        <li :class="{ active: selectedComponent === 'Announcement' }">
          <a href="#" @click="showComponent('Announcement')">
            <i
              class="fa fa-bullhorn"
              aria-hidden="true"
              style="padding: 0.5rem 0.8rem"
            ></i>
            <span class="text">Announcement</span>
          </a>
        </li>

        <li :class="{ active: selectedComponent === 'AddAdmin' }">
          <a href="#" @click="showComponent('AddAdmin')">
            <i
              class="fa fa-plus"
              aria-hidden="true"
              style="padding: 0.5rem 0.8rem"
            ></i>
            <span class="text">Add Admin</span>
          </a>
        </li>
        <li :class="{ active: selectedComponent === 'AddUser' }">
          <a href="#" @click="showComponent('AddUser')">
            <i
              class="fa fa-plus"
              aria-hidden="true"
              style="padding: 0.5rem 0.8rem"
            ></i>
            <span class="text">Add User</span>
          </a>
        </li>
      </div>
      <li>
        <a href="#" id="rate-btn" @click="toggleButtons3">
          <div class="rate">
            <i
              class="fa fa-briefcase"
              aria-hidden="true"
              style="padding: 0.5rem 0.8rem"
            ></i>
            <span class="text">Manage Offices</span>
          </div>
          <i v-if="showButtons3" class="fa-solid fa-chevron-down"></i>
          <i v-else class="fa-solid fa-chevron-right"></i>
        </a>
      </li>
      <div v-show="showButtons3" class="li-div">
        <li :class="{ active: selectedComponent === 'AddPPO' }">
          <a href="#" @click="showComponent('AddPPO')">
            <i
              class="fa fa-briefcase"
              aria-hidden="true"
              style="padding: 0.5rem 0.8rem"
            ></i>
            <span class="text">Manage PPO</span>
          </a>
        </li>
        <li :class="{ active: selectedComponent === 'AddRMFB' }">
          <a href="#" @click="showComponent('AddRMFB')">
            <i
              class="fa fa-briefcase"
              aria-hidden="true"
              style="padding: 0.5rem 0.8rem"
            ></i>
            <span class="text">Manage RMFB</span>
          </a>
        </li>
        <li :class="{ active: selectedComponent === 'AdminOccidental' }">
          <a href="#" @click="showComponent('AdminOccidental')">
            <i
              class="fa fa-briefcase"
              aria-hidden="true"
              style="padding: 0.5rem 0.8rem"
            ></i>
            <span class="text">Manage MPS</span>
          </a>
        </li>
      </div>
      <li :class="{ active: selectedComponent === 'ManageAdmin' }">
        <a href="#" @click="showComponent('ManageAdmin')">
          <i
            class="fa fa-user-circle"
            aria-hidden="true"
            style="padding: 0.5rem 0.8rem"
          ></i>
          <span class="text">Manage Admin</span>
        </a>
      </li>

      <li>
        <a href="https://mail.google.com/" target="blank">
          <i
            class="fa fa-envelope"
            aria-hidden="true"
            style="padding: 0.5rem 0.8rem"
          ></i>
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
    <Navbar />
    <!-- NAVBAR -->

    <!-- MAIN -->
    <main id="main">
      <Dashboard v-if="selectedComponent === 'Dashboard'" />
      <Ratings v-if="selectedComponent === 'Ratings'" />
      <PPORates v-if="selectedComponent === 'PPORates'" />
      <RMFBRates v-if="selectedComponent === 'RMFBRates'" />
      <MPSRates v-if="selectedComponent === 'MPSRates'" />
      <ManageUser v-if="selectedComponent === 'ManageUser'" />
      <ManageAdmin v-if="selectedComponent === 'ManageAdmin'" />
      <Announcement v-if="selectedComponent === 'Announcement'" />
      <AddPPO v-if="selectedComponent === 'AddPPO'" />
      <AddRMFB v-if="selectedComponent === 'AddRMFB'" />
      <AdminOccidental v-if="selectedComponent === 'AdminOccidental'" />
      <AddAdmin v-if="selectedComponent === 'AddAdmin'" />
      <AddUser v-if="selectedComponent === 'AddUser'" />
      <ManageRate v-if="selectedComponent === 'ManageRate'" />
    </main>
    <!-- MAIN -->
  </section>

  <div
    class="modal fade"
    id="logout-modal"
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
  <!-- CONTENT ---->
</template>

<script>
import axios from "axios";
import Navbar from "../components/Navbar.vue";
import Dashboard from "../components/Dashboard.vue";
import Ratings from "../components/Ratings.vue";
import ManageUser from "../components/ManageUser.vue";
import ManageAdmin from "../components/ManageAdmin.vue";
import Announcement from "../components/AdminGmail.vue";
import PPORates from "../components/PPO.vue";
import RMFBRates from "../components/RMFB.vue";
import MPSRates from "../components/MPS-CPS.vue";
import AddPPO from "../components/AddPPO.vue";
import AddRMFB from "../components/AddRMFB.vue";
import AdminOccidental from "../components/AddMPS.vue";
import AddUser from "../components/AddUser.vue";
import AddAdmin from "../components/AddAdmin.vue";
import ManageRate from "../components/ManageRate.vue";
import router from "@/router";
import { Modal } from "bootstrap";

export default {
  components: {
    Navbar,
    Dashboard,
    Ratings,
    ManageUser,
    ManageAdmin,
    Announcement,
    PPORates,
    RMFBRates,
    MPSRates,
    AddPPO,
    AddRMFB,
    AdminOccidental,
    AddUser,
    AddAdmin,
    ManageRate,
  },

  data() {
    return {
      selectedComponent: "Dashboard",
      showButtons: false,
      showButtons2: false,
      showButtons3: false,
      userData: "",
    };
  },
  watch: {
    selectedComponent: function (newComponent) {
      // Update document title based on the selected component
      document.title = "EUPER - " + newComponent;
    },
  },

  mounted() {
    this.fetchUserData();
  },
  methods: {
    confirmLogout() {
      const modalElement = document.getElementById("logout-modal");
      const modalInstance = new Modal(modalElement);
      modalInstance.show();
    },
    logout() {
      const modalElement = document.getElementById("logout-modal");
      const modalInstance = new Modal(modalElement);
      const backdrop = document.querySelector(".modal-backdrop");
      if (backdrop) {
        backdrop.remove();
      }

      modalInstance.hide();

      sessionStorage.removeItem("id");
      router.push("/");
    },
    showComponent(componentName) {
      this.selectedComponent = componentName;
    },
    toggleButtons() {
      this.showButtons = !this.showButtons;
      this.showButtons2 = false;
      this.showButtons3 = false;
    },
    toggleButtons2() {
      this.showButtons2 = !this.showButtons2;
      this.showButtons = false;
      this.showButtons3 = false;
    },
    toggleButtons3() {
      this.showButtons3 = !this.showButtons3;
      this.showButtons2 = false;
      this.showButtons = false;
    },

    async fetchUserData() {
      const storedUserId = sessionStorage.getItem("id");
      if (storedUserId) {
        try {
          const response = await axios.get(`/getUserData/${storedUserId}`);
          if (response.status === 200) {
            this.userData = response.data;
            // console.log(response.data);
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
