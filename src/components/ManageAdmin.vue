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
          <tr v-for="adminInfo in AdminsInfo" :key="adminInfo.admin_id">
            <td>{{ adminInfo.username }}</td>
            <td></td>
            <td>{{ adminInfo.email }}</td>
            <td>{{ adminInfo.phone_no }}</td>
            <td class="td-btn">
              <button class="pen-btn" @click="openForm(adminInfo)">
                <i class="fa-solid fa-pen fa-lg"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <form
    class="form"
    id="modal-form"
    :style="{ display: formVisible ? 'block' : 'none' }"
  >
    <input
      v-model="selectedAdmin.username"
      type="text"
      placeholder="Username"
      class="input"
    />
    <input
      v-model="selectedAdmin.password"
      type="text"
      placeholder="Password"
      class="input"
    />
    <input
      v-model="selectedAdmin.email"
      type="text"
      placeholder="Email"
      class="input"
    />
    <input
      v-model="selectedAdmin.phone_no"
      type="text"
      placeholder="Phone No."
      class="input"
    />
    <div class="modal-buttons">
      <button @click.prevent="saveAdmin">Save</button>
      <button @click.prevent="closeForm">Close</button>
    </div>
  </form>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      AdminsInfo: [],
      formVisible: false,
      selectedAdmin: {
        admin_id: null,
        username: "",
        password: "",
        email: "",
        phone_no: "",
      },
    };
  },

  created() {
    this.getAdminsInfo();
  },

  methods: {
    async getAdminsInfo() {
      try {
        const response = await axios.get("/getAdmins");
        this.AdminsInfo = response.data;
      } catch (error) {
        console.error("Error fetching admin data:", error);
      }
    },

    openForm(adminInfo) {
      this.selectedAdmin = { ...adminInfo };
      this.formVisible = true;
    },

    async saveAdmin() {
      try {
        const response = await axios.post("/api/saveAdmin", this.selectedAdmin);

        if (response.status === 200) {
          const responseData = response.data;

          if (responseData && responseData.success) {
            console.log(responseData.message);
            this.formVisible = false;
            this.getAdminsInfo();
          } else {
            console.error("Save failed:", responseData.message);
          }
        } else {
          console.error(`Unexpected response status: ${response.status}`);
        }
      } catch (error) {
        console.error("Error saving admin:", error);
      }
    },

    closeForm() {
      this.formVisible = false;
    },
  },
};
</script>

<style>
#modal-form {
  position: absolute;
  width: 50%;
  top: 15%;
  left: 25%;
  display: none;
}

.modal-buttons {
  display: flex;
  justify-content: end;
  width: 100%;
  gap: 1rem;
  padding-top: 1rem;
}
</style>
