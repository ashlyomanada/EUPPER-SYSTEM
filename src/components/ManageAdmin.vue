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
          <tr>
            <td>{{ selectedAdmin.username }}</td>
            <td></td>
            <td>{{ selectedAdmin.email }}</td>
            <td>{{ selectedAdmin.phone_no }}</td>
            <td class="td-btn">
              <button class="pen-btn" @click="openForm()">
                <i class="fa-solid fa-pen fa-lg"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="modal-background" :class="{ 'dim-overlay': formVisible }">
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
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      formVisible: false,
      selectedAdmin: {
        admin_id: null,
        username: "",
        password: "",
        email: "",
        phone_no: "",
      },
      storedId: "",
    };
  },

  mounted() {
    this.storedId = sessionStorage.getItem("id");
    this.getAdminsData();
  },

  methods: {
    async getAdminsData() {
      const adminStoredId = sessionStorage.getItem("id");
      if (adminStoredId) {
        try {
          const response = await axios.get(`/getUserData/${adminStoredId}`);
          if (response.status === 200) {
            this.selectedAdmin = response.data;
          }
        } catch (e) {
          console.error(e);
        }
      }
    },

    openForm() {
      this.formVisible = true;
    },

    async saveAdmin() {
      try {
        const response = await axios.post(
          `/updateAdminInformation/${this.storedId}`,
          this.selectedAdmin
        );

        if (response.status === 200) {
          const responseData = response.data;
          console.log(this.selectedAdmin);

          if (responseData && responseData.success) {
            console.log(responseData.message);
            this.formVisible = false;
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
.dim-overlay {
  position: fixed;
  display: flex;
  justify-content: center;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(
    0,
    0,
    0,
    0.5
  ); /* Adjust the last value for the desired transparency */
  z-index: 1;
  /* Make sure the overlay is above other elements */
}
#modal-form {
  position: absolute;
  width: 50%;
  top: 25%;
  left: 37%;
  display: none;
  z-index: 2;
}

.modal-buttons {
  display: flex;
  justify-content: end;
  width: 100%;
  gap: 1rem;
  padding-top: 1rem;
}
</style>
