<template>
  <div class="table-data">
    <div class="order">
      <div class="head d-flex flex-column">
        <h3>List of Officer will sign</h3>

        <div class="manageItems d-flex justify-content-end">
          <button class="btn btn-primary" @click.prevent="openAddOfficer">
            Add Officer
          </button>
        </div>
      </div>
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Office</th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="UserInfos in UsersInfo" :key="UserInfos.officerId">
            <td>{{ UserInfos.name }}</td>
            <td>{{ UserInfos.office }}</td>
            <td class="td-btn d-flex justify-content-center gap-2">
              <button class="btn btn-warning" @click="editOfficer(UserInfos)">
                <i class="fa-solid fa-pen fa-lg"></i>
              </button>
              <button
                class="btn btn-danger"
                @click="deleteOfficer(UserInfos.officerId)"
              >
                <i class="fa-solid fa-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <h5 v-if="dataFetched" style="text-align: center">No Officers Found</h5>
    </div>
  </div>

  <div class="alert-container">
    <v-alert v-if="alertMessage" :type="errorType" class="error-message">{{
      alertMessage
    }}</v-alert>
  </div>

  <!-- Bootstrap Modal for Adding User -->
  <div
    class="modal fade"
    id="addOfficerModal"
    tabindex="-1"
    aria-labelledby="editUserModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div
        class="modal-content text-start"
        style="background: var(--light); color: var(--dark)"
      >
        <div class="modal-header">
          <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
        </div>
        <div class="modal-body text-start">
          <form @submit.prevent="saveOfficer">
            <div class="mb-3">
              <label class="form-label text-start" for="username"
                >Username</label
              >
              <input
                v-model="username"
                type="text"
                class="form-control"
                id="username"
                placeholder="Username"
                required
                style="background: var(--light); color: var(--dark)"
              />
            </div>
            <div class="mb-3">
              <label class="form-label text-start" for="office">Office</label>
              <input
                v-model="office"
                type="text"
                class="form-control"
                id="office"
                placeholder="Office"
                required
                style="background: var(--light); color: var(--dark)"
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

  <!-- Bootstrap Modal for Editing User -->
  <div
    class="modal fade"
    id="editOfficerModal"
    tabindex="-1"
    aria-labelledby="editUserModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div
        class="modal-content text-start"
        style="background: var(--light); color: var(--dark)"
      >
        <div class="modal-header">
          <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
        </div>
        <div class="modal-body text-start">
          <form @submit.prevent="saveUser">
            <div class="mb-3">
              <label class="form-label text-start" for="username"
                >Username</label
              >
              <input
                v-model="selectedUser.officerId"
                type="hidden"
                class="form-control"
                id="username"
                placeholder="Username"
                required
                style="background: var(--light); color: var(--dark)"
              />
              <input
                v-model="selectedUser.name"
                type="text"
                class="form-control"
                id="username"
                placeholder="Username"
                required
                style="background: var(--light); color: var(--dark)"
              />
            </div>
            <div class="mb-3">
              <label class="form-label text-start" for="office">Office</label>
              <input
                v-model="selectedUser.office"
                type="text"
                class="form-control"
                id="office"
                placeholder="Office"
                required
                style="background: var(--light); color: var(--dark)"
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
      UsersInfo: [],
      username: "",
      office: "",
      selectedUser: {
        officerId: null,
        name: "",
        office: "",
      },
      dataFetched: false,
      alertMessage: "",
      errorType: "",
    };
  },

  created() {
    this.getUsersInfo();
  },

  mounted() {
    this.getUsersInfo();
    setInterval(() => {
      this.alertMessage = null;
    }, 3000);
  },

  methods: {
    async getUsersInfo() {
      try {
        const UsersInfo = await axios.get("getOfficers");
        this.UsersInfo = UsersInfo.data;
        if (this.UsersInfo.length > 0) {
          this.dataFetched = false;
        } else {
          this.dataFetched = true;
        }
      } catch (e) {
        console.log(e);
      }
    },

    openAddOfficer() {
      const modalElement = document.getElementById("addOfficerModal");
      const modalInstance = new Modal(modalElement);
      modalInstance.show();
    },

    async saveOfficer() {
      const modalElement = document.getElementById("addOfficerModal");
      const modalInstance =
        Modal.getInstance(modalElement) || new Modal(modalElement);

      try {
        const response = await axios.post("/saveOfficer", {
          Name: this.username,
          Office: this.office,
        });

        if (response.status === 200) {
          this.alertMessage = "Successfully saved Officer";
          this.getUsersInfo();
          this.errorType = "success";
          this.username = "";
          this.office = "";
          modalInstance.hide();
        }
      } catch (e) {
        if (e.response.status === 404) {
          this.alertMessage = "Please enter valid name and office name";
          this.errorType = "error";
          modalInstance.hide();
        } else if (e.response.status === 500) {
          this.alertMessage = "Server error, please try again later";
          this.errorType = "error";
          modalInstance.hide();
        } else {
          this.alertMessage =
            "Please check your internet connection and try again later";
          this.errorType = "error";
          modalInstance.hide();
        }
      }
    },

    editOfficer(UsersInfo) {
      this.selectedUser = { ...UsersInfo };
      this.editUserModal = new Modal(
        document.getElementById("editOfficerModal")
      );
      this.editUserModal.show();
    },

    async saveUser() {
      try {
        const response = await axios.post(
          "/updateOfficerInfo",
          this.selectedUser
        );

        if (response.status === 200) {
          this.editUserModal.hide();
          this.getUsersInfo();
          this.alertMessage = "Officer Info has been updated";
          this.errorType = "success";
        }
      } catch (error) {
        if (error.response.status === 404) {
          this.alertMessage =
            "Please check the details carefully before updating";
          this.errorType = "error";
        } else if (error.response.status === 500) {
          this.alertMessage = "Server error, Please try again later";
          this.errorType = "error";
        } else {
          this.alertMessage =
            "Please check internet connection and try again later";
          this.errorType = "error";
        }
      }
    },

    async deleteOfficer(officerId) {
      try {
        const response = await axios.post("/deleteOfficer", {
          officerId: officerId,
        });

        if (response.status === 200) {
          this.alertMessage = "Successfully deleted officer";
          this.errorType = "success";
          this.getUsersInfo();
        }
      } catch (error) {
        if (error.response.status === 404) {
          this.alertMessage = "Please select valid officer to be deleted";
          this.errorType = "error";
        } else if (error.response.status === 500) {
          this.alertMessage = "Server error, Please try again later";
          this.errorType = "error";
        } else {
          this.alertMessage =
            "Please check internet connection and try again later";
          this.errorType = "error";
        }
      }
    },
  },
};
</script>

<style scoped>
.labels {
  color: var(--dark);
}
.td-btn {
  text-align: center;
}
.fa-solid {
  font-weight: 600;
}
.users-btn {
  color: rgb(47, 212, 47);
}
.green-btn {
  color: rgb(47, 212, 47);
}
.pen-btn {
  color: rgb(233, 70, 70);
}
.red-btn {
  color: rgb(233, 70, 70);
}
@media screen and (max-width: 768px) {
  .manageItems {
    align-items: center;
  }
}
</style>
