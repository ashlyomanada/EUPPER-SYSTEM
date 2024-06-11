<template>
  <div class="table-data">
    <div class="order">
      <div class="head">
        <h3>Manage Admin</h3>
      </div>
      <table>
        <thead>
          <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Phone No.</th>
            <th>Update</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>{{ selectedAdmin.username }}</td>
            <td>{{ selectedAdmin.email }}</td>
            <td>{{ selectedAdmin.phone_no }}</td>
            <td class="td-btn">
              <button
                class="btn btn-primary"
                @click="openForm(selectedAdmin)"
                data-bs-toggle="modal"
                data-bs-target="#adminModal"
              >
                <i class="fa-solid fa-pen fa-lg"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Bootstrap Modal -->
  <div
    class="modal fade"
    id="adminModal"
    tabindex="-1"
    aria-labelledby="adminModalLabel"
    aria-hidden="true"
    ref="adminModal"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div
        class="modal-content"
        style="background: var(--light); color: var(--dark)"
      >
        <div class="modal-header">
          <h5 class="modal-title" id="adminModalLabel">Edit Admin</h5>
        </div>
        <div class="modal-body text-start">
          <form @submit.prevent="saveAdmin">
            <div class="mb-3">
              <label for="username" class="form-label text-start"
                >Username</label
              >
              <input
                style="background: var(--light); color: var(--dark)"
                v-model="selectedUser.username"
                type="text"
                class="form-control"
                id="username"
                placeholder="Username"
              />
            </div>
            <div class="mb-3">
              <label for="email" class="form-label text-start">Email</label>
              <input
                style="background: var(--light); color: var(--dark)"
                v-model="selectedUser.email"
                type="email"
                class="form-control"
                id="email"
                placeholder="Email"
              />
            </div>
            <div class="mb-3">
              <label for="phone_no" class="form-label text-start"
                >Phone No.</label
              >
              <input
                style="background: var(--light); color: var(--dark)"
                v-model="selectedUser.phone_no"
                type="text"
                class="form-control"
                id="phone_no"
                placeholder="Phone No."
              />
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">Save</button>
              <button
                type="button"
                class="btn btn-danger"
                data-bs-dismiss="modal"
              >
                Close
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { Modal } from "bootstrap";

export default {
  data() {
    return {
      selectedAdmin: "",
      storedId: "",
      selectedUser: {
        username: "",
        email: "",
        phone_no: "",
      },
      modalInstance: null,
    };
  },

  mounted() {
    this.storedId = sessionStorage.getItem("id");
    this.getAdminsData();
    this.initializeModal();
  },

  methods: {
    async getAdminsData() {
      const adminStoredId = this.storedId;
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

    openForm(selectedAdmin) {
      this.selectedUser = { ...selectedAdmin };
    },

    async saveAdmin() {
      try {
        const response = await axios.post("/api/saveUser", this.selectedUser);

        if (response.status === 200) {
          const responseData = response.data;
          this.getAdminsData();

          if (responseData && responseData.success) {
            console.log(responseData.message);
            this.closeForm();
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
      if (this.modalInstance) {
        this.modalInstance.hide();
      }
    },

    initializeModal() {
      const modalElement = this.$refs.adminModal;
      if (modalElement) {
        this.modalInstance = new Modal(modalElement);
      }
    },
  },
};
</script>

<style>
.labels {
  color: var(--dark);
}
</style>
