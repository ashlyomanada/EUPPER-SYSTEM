<template>
  <div class="table-data">
    <div class="order">
      <div class="head">
        <h3>List of Users</h3>
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
            <td>Enable</td>
            <td class="td-btn">
              <button class="users-btn">
                <i class="fa-solid fa-power-off fa-lg"></i>
              </button>
            </td>
            <td class="td-btn">
              <button class="pen-btn">
                <i class="fa-solid fa-pen fa-lg"></i>
              </button>
            </td>
            <td class="td-btn">
              <button class="users-btn">
                <i class="fa-solid fa-phone fa-lg"></i>
              </button>
            </td>
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

<style>
.td-btn {
  text-align: center;
}
.fa-solid {
  font-weight: 600;
}
.users-btn {
  color: rgb(47, 212, 47);
}
.pen-btn {
  color: rgb(233, 70, 70);
}
</style>
