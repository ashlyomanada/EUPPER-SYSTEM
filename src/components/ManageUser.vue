<template>
  <div class="table-data">
    <div class="order">
      <div class="head">
        <h3>Manage User</h3>
        <i class="bx bx-search"></i>
        <i class="bx bx-filter"></i>
      </div>
      <table>
        <thead>
          <tr>
            <th>Username</th>
            <th>Office</th>
            <th>Phone No.</th>
            <th>Email</th>
            <th>Status</th>
            <th>Action</th>
            <th>Update</th>
            <th>Contact User</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="UsersInfo in UsersInfo" :key="UsersInfo.user_id">
            <td>{{ UsersInfo.username }}</td>
            <td>{{ UsersInfo.office }}</td>
            <td>{{ UsersInfo.phone_no }}</td>
            <td>{{ UsersInfo.email }}</td>
            <td>Status</td>
            <td><button>Enable</button></td>
            <td><button>Update</button></td>
            <td><button>Contact</button></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import axios from "axios";
export default {
  data() {
    return {
      UsersInfo: [],
    };
  },

  created() {
    this.getUsersInfo();
  },
  methods: {
    async getUsersInfo() {
      try {
        const UsersInfo = await axios.get("getUsers");
        this.UsersInfo = UsersInfo.data;
      } catch (e) {
        console.log(e);
      }
    },
  },
};
</script>

<style></style>
