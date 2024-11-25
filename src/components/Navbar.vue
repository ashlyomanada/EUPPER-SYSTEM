<template>
  <nav>
    <i class="bx bx-menu" @click.prevent="toggleMenu"></i>
    <div class="w-50 d-flex">
      <a href="#" class="nav-link" style="color: var(--dark)"
        >PRO MIMAROPA E-UPER SYSTEM</a
      >
    </div>

    <div class="nav-items">
      <span class="time2">{{ currentDateTime }}</span>
      <input type="checkbox" id="switch-mode" hidden />
      <label
        for="switch-mode"
        @click="switchToggle"
        class="switch-mode"
      ></label>
      <!-- <a href="#" class="profile"> </a> -->
      <div
        class="notif-container"
        type="button"
        data-bs-toggle="modal"
        data-bs-target="#exampleModal"
      >
        <i class="fa-regular fa-bell"></i>
        <span class="notif-count">{{ countNotif ? countNotif : 0 }}</span>
      </div>
    </div>
  </nav>

  <!-- Modal -->
  <div
    class="modal fade"
    id="exampleModal"
    tabindex="-1"
    aria-labelledby="exampleModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered" style="color: var(--dark)">
      <div class="modal-content" style="background: var(--light)">
        <div class="modal-header d-flex justify-content-between">
          <h6>NOTIFICATIONS</h6>
          <a
            class="viewAllBtn"
            @click.prevent="markAsRead"
            style="color: var(--blue); cursor: pointer"
            >Mark all as read</a
          >
        </div>
        <div class="modal-body">
          <div class="notifContents" style="overflow-y: auto; height: 250px">
            <div v-if="allNotif.length === 0" class="text-center">
              No Notification yet
            </div>
            <div v-for="notif in allNotif" :key="notif.id">
              <div class="d-flex flex-column border-primary-subtle">
                <p
                  style="
                    font-size: 0.8rem;
                    background-color: var(--grey);
                    padding: 1rem;
                    border-radius: 0.5rem;
                    text-align: justify;
                  "
                  :class="notif.status === 'Unread' ? 'bold' : 'default'"
                >
                  {{ notif.title }}, {{ notif.message }}
                  <br />
                  <span
                    :class="notif.status === 'Unread' ? 'bold' : 'default'"
                    style="float: right; padding-top: 0.5rem"
                    >{{ notif.created_at }}</span
                  >
                </p>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
// import { Modal } from "bootstrap";
import { useToast } from "vue-toastification";
import "vue-toastification/dist/index.css";
import NotificationToast from "@/components/NotificationToast.vue";
import { Modal } from "bootstrap";

export default {
  data() {
    return {
      profilePic: "",
      currentDateTime: "",
      dateTimeInterval: null, // Interval handler
      showNotif: false,
      countNotif: 0,
      allNotif: [],
    };
  },

  mounted() {
    this.profile(); // Fetch user profile
    this.updateCurrentDateTime(); // Update initial datetime
    this.dateTimeInterval = setInterval(() => {
      this.updateCurrentDateTime();
      this.getNotifCount();
    }, 1000);
  },

  created() {
    this.getNotifCount();
    this.getNotifications();
  },

  beforeDestroy() {
    // Clear the interval to prevent memory leaks
    clearInterval(this.dateTimeInterval);
  },

  methods: {
    async markAsRead() {
      const response = await axios.post("markAsRead", {
        Status: "Read",
      });
    },

    async getNotifCount() {
      const response = await axios.get("getNotifications");
      this.countNotif = response.data.totalNotif;
      this.allNotif = response.data.notifications;
    },

    async viewAllNotif() {
      const response = await axios.get("viewAllNotifications");
      this.allNotif = response.data.notifications;
    },

    async getNotifications() {
      const response = await axios.get("getNotifications");
      const notifications = response.data.unreadNotifications;
      this.allNotif = response.data.notifications;
      // console.log("All Notifications:", this.allNotif);
      const toast = useToast();

      notifications.forEach((notif) => {
        toast.success({
          component: NotificationToast,
          props: {
            id: notif.id,
            title: notif.title,
            message: notif.message,
          },
        });
      });
    },

    showNotifModal() {
      this.showNotif = !this.showNotif;
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
.fa-bell {
  font-size: 1.5rem;
  color: var(--dark);
}
.notif-container {
  position: relative;
  padding: 0.5rem;
  cursor: pointer;
}

.notifContents {
  scrollbar-width: thin; /* For Firefox */
  scrollbar-color: #555555 var(--light); /* For Firefox */
  padding: 0.5rem;
}

.notifContents::-webkit-scrollbar-track {
  background-color: rgba(244, 244, 244, 0.5); /* For Chrome/Safari/Edge */
}

/* Style for the scrollbar thumb */
.notifContents::-webkit-scrollbar-thumb {
  background-color: rgba(244, 244, 244, 0.5); /* For Chrome/Safari/Edge */
  border-radius: 5px;
}

.notif-count {
  position: absolute;
  top: 0;
  right: 0;
  background: var(--blue);
  color: white;
  border-radius: 50%;
  font-size: 10px;
  display: flex;
  justify-content: center;
  width: 15px;
}
.time2 {
  color: var(--dark);
}

.nav-items {
  width: 100%;
  display: flex;
  justify-content: flex-end;
  gap: 2rem;
  align-items: center;
}
.vertical-menu {
  width: 350px; /* Set a width if you like */
  position: absolute;
  top: 8.5vh;
  right: 1.5rem;
  z-index: 200;
  padding: 1rem;
  background-color: var(--light); /* Grey background color */
  max-height: 360px;
  border-radius: 0.5rem;
  color: var(--dark);
  -webkit-box-shadow: 0 15px 30px 0 rgba(0, 0, 0, 0.2);
  box-shadow: 0 15px 30px 0 rgba(0, 0, 0, 0.2);
  transition: ease;
  display: flex;
  flex-direction: column;
}

.bold {
  font-weight: 600;
}

.default {
  font-weight: normal;
}

.vertical-menu a {
  display: block; /* Make the links appear below each other */
  padding: 12px; /* Add some padding */
  text-decoration: none; /* Remove underline from links */
  cursor: pointer;
}

.viewAllBtn:hover {
  color: rgb(41, 143, 245);
}

@media screen and (max-width: 600px) {
  .time2 {
    display: none;
  }
}
</style>
