<template>
  <div class="table-data">
    <div class="order">
      <div class="head">
        <h3>Manage Admin</h3>
        <i class="bx bx-search"></i>
        <i class="bx bx-filter"></i>
      </div>
      <table>
        <thead>
          <tr>
            <th>Username</th>
            <th>Password</th>
            <th>Email</th>
            <th>Phone No.</th>
            <th>Update</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="AdminsInfo in AdminsInfo" :key="AdminsInfo.admin_id">
            <td>{{ AdminsInfo.username }}</td>
            <td>{{ AdminsInfo.password }}</td>
            <td>{{ AdminsInfo.email }}</td>
            <td>{{ AdminsInfo.phone_no }}</td>
            <td>Update</td>
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
      AdminsInfo: [],
    };
  },

  created() {
    this.getAdminsInfo();
  },
  methods: {
    async getAdminsInfo() {
      try {
        const AdminsInfo = await axios.get("getAdmins");
        this.AdminsInfo = AdminsInfo.data;
      } catch (e) {
        console.log(e);
      }
    },
  },
};
</script>

<style></style>
