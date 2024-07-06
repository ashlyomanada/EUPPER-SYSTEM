<template>
  <nav>
    <i class="bx bx-menu" @click="toggleMenu"></i>
    <div class="w-50 d-flex">
      <a href="#" class="nav-link">PRO MIMAROPA E-UPER SYSTEM</a>
    </div>

    <div class="nav-items">
      <span class="time2">{{ currentDateTime }}</span>
      <input type="checkbox" id="switch-mode" hidden />
      <label
        for="switch-mode"
        @click="switchToggle"
        class="switch-mode"
      ></label>
      <a href="#" class="profile"> </a>
    </div>
  </nav>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      profilePic: "",
      currentDateTime: "",
      dateTimeInterval: null, // Interval handler
    };
  },

  mounted() {
    this.profile(); // Fetch user profile
    this.updateCurrentDateTime(); // Update initial datetime

    // Set interval to update currentDateTime every second
    this.dateTimeInterval = setInterval(() => {
      this.updateCurrentDateTime();
    }, 1000);
  },

  beforeDestroy() {
    // Clear the interval to prevent memory leaks
    clearInterval(this.dateTimeInterval);
  },

  methods: {
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

    async profile() {
      const storedUserId = sessionStorage.getItem("id");
      if (storedUserId) {
        try {
          const response = await axios.get(`/getUserData/${storedUserId}`);
          if (response.status === 200) {
            const userdata = response.data;
            this.profilePic = userdata.image;
          }
        } catch (e) {
          console.log(e);
        }
      }
    },

    toggleMenu() {
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

      // TOGGLE SIDEBAR
      const menuBar = document.querySelector("#content nav .bx.bx-menu");
      const sidebar = document.getElementById("sidebar");
      const logo = document.getElementById("logo");
      const adminName = document.getElementById("adminName");

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

      const searchButton = document.querySelector(
        "#content nav form .form-input button"
      );
      const searchButtonIcon = document.querySelector(
        "#content nav form .form-input button .bx"
      );
      const searchForm = document.querySelector("#content nav form");

      if (window.innerWidth < 768) {
        sidebar.classList.add("hide");
        logo.style.height = "38px";
        adminName.style.visibility = "hidden";
      }
    },

    switchToggle() {
      const switchMode = document.getElementById("switch-mode");
      const main = document.getElementById("main");

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
  },
};
</script>

<style>
.time2 {
  color: var(--dark);
}

.nav-items {
  width: 40%;
  display: flex;
  justify-content: space-evenly;
}

.bx {
  font-family: boxicons !important;
  font-weight: 400;
  font-style: normal;
  font-variant: normal;
  line-height: 1;
  text-rendering: auto;
  display: inline-block;
  text-transform: none;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  font-size: 25px;
}

@media screen and (max-width: 600px) {
  .time2 {
    display: none;
  }
}
</style>
