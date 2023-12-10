<template>
  <div class="head-title">
    <div class="left">
      <h1 id="dashboardTitle">Dashboard</h1>
    </div>
  </div>

  <ul class="box-info">
    <li>
      <i class="bx bxs-calendar-check"></i>
      <span class="text">
        <h3>{{ ratingCount }}</h3>
        <p>Ratings</p>
      </span>
    </li>
    <li>
      <i class="bx bxs-group"></i>
      <span class="text">
        <h3>{{ userCount }}</h3>
        <p>Users</p>
      </span>
    </li>
    <li>
      <i class="bx bxs-calendar-check"></i>
      <span class="text">
        <h3>0</h3>
        <p>Announcements</p>
      </span>
    </li>
  </ul>
</template>

<script>
export default {
  data() {
    return {
      ratingCount: 0,
      userCount: 0,
      // Add similar data properties for Users and Announcements if needed
    };
  },
  mounted() {
    this.fetchRatingCount();
    this.fetchUserCount();
  },
  methods: {
    async fetchRatingCount() {
      try {
        const response = await fetch("http://localhost:8080/countUserRatings");
        const data = await response.json();
        this.ratingCount = data.count;
        //console.log(data);
      } catch (error) {
        console.error("Error fetching rating count:", error);
      }
    },
    async fetchUserCount() {
      try {
        const response = await fetch("http://localhost:8080/countUser");
        const data = await response.json();
        this.userCount = data.count;
        //console.log(data);
      } catch (error) {
        console.error("Error fetching rating count:", error);
      }
    },
  },
};
</script>
<style></style>
