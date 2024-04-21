<template>
  <nav>
    <i class="bx bx-menu"></i>
    <div class="w-50 d-flex">
      <a href="#" class="nav-link">PRO MIMAROPA E-UPER SYSTEM</a>
    </div>

    <div class="nav-items">
      <span class="time2">{{ currentDateTime }}</span>
      <input type="checkbox" id="switch-mode" hidden />
      <label for="switch-mode" class="switch-mode"></label>
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

@media screen and (max-width: 600px) {
  .nav-items {
    width: 60%;
    display: flex;
    justify-content: space-evenly;
  }
}
</style>
