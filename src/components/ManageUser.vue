<template>
  <div class="table-data">
    <div class="order">
      <div class="head">
        <h3>List of Users</h3>
        <input
          type="datetime-local"
          v-model="dueDate"
          style="background: var(--light); color: var(--dark)"
        />
        <button class="find" @click="SetDate">Set Closing</button>
        <button class="find" @click="changeAllUserStatus">
          <i class="fa-solid fa-power-off"></i>
          Change All User Status
        </button>
        <div class="d-flex gap-2">
          <button class="find d-flex align-items-center" @click="findData">
            <i class="bx bx-search"></i>Find
          </button>
          <input
            v-model="searchText"
            type="text"
            class="year"
            id="searchText"
          />
        </div>
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
            <td>{{ UsersInfo.status }}</td>
            <td class="td-btn">
              <button
                class="users-btn"
                @click="toggleUserStatus(UsersInfo.user_id)"
              >
                <i
                  class="fa-solid fa-power-off fa-lg"
                  :class="{
                    'green-btn': UsersInfo.status === 'Enable',
                    'red-btn': UsersInfo.status === 'Disable',
                  }"
                ></i>
              </button>
            </td>
            <td class="td-btn">
              <button class="pen-btn" @click="openForm(UsersInfo)">
                <i class="fa-solid fa-pen fa-lg"></i>
              </button>
            </td>
            <td class="td-btn">
              <button class="users-btn" @click="openForm2(UsersInfo)">
                <i class="fa-solid fa-phone fa-lg"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Bootstrap Modal for Editing User -->
  <div
    class="modal fade"
    id="editUserModal"
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
                v-model="selectedUser.username"
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
            <div class="mb-3">
              <label class="form-label text-start" for="email">Email</label>
              <input
                v-model="selectedUser.email"
                type="email"
                class="form-control"
                id="email"
                placeholder="Email"
                required
                style="background: var(--light); color: var(--dark)"
              />
            </div>
            <div class="mb-3">
              <label class="form-label text-start" for="phone_no"
                >Phone No.</label
              >
              <input
                v-model="selectedUser.phone_no"
                type="text"
                class="form-control"
                id="phone_no"
                placeholder="Phone No."
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

  <!-- Bootstrap Modal for Sending SMS -->
  <div
    class="modal fade"
    id="sendSmsModal"
    tabindex="-1"
    aria-labelledby="sendSmsModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div
        class="modal-content"
        style="background: var(--light); color: var(--dark)"
      >
        <div class="modal-header">
          <h5 class="modal-title" id="sendSmsModalLabel">Send SMS</h5>
        </div>
        <div class="modal-body text-start">
          <form @submit.prevent="sendSms">
            <div class="mb-3">
              <label class="form-label text-start" for="to">To</label>
              <input
                v-model="selectedUser.phone_no"
                type="text"
                class="form-control"
                id="to"
                readonly
                required
                style="background: var(--light); color: var(--dark)"
              />
            </div>
            <div class="mb-3">
              <label class="form-label text-start" for="from">From</label>
              <input
                type="text"
                class="form-control"
                id="from"
                value="PRO MIMAROPA ADMINISTRATOR"
                readonly
                required
                style="background: var(--light); color: var(--dark)"
              />
            </div>
            <div class="mb-3">
              <label class="form-label text-start" for="message">Message</label>
              <textarea
                v-model="messageContent"
                class="form-control"
                id="message"
                cols="30"
                rows="5"
                placeholder="Enter message here"
                required
                style="background: var(--light); color: var(--dark)"
              ></textarea>
            </div>
            <div class="modal-footer">
              <button
                type="submit"
                class="btn btn-primary"
                :disabled="sendingInProgress"
              >
                Send
              </button>
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

  <!-- Bootstrap Modal for Success Alert -->
  <div
    class="modal fade"
    id="successModal"
    tabindex="-1"
    aria-labelledby="successModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-body text-center">
          <img class="checkImg" src="./img/check2.gif" alt="Success" />
          <h1 class="alertContent">Successfully Sent</h1>
          <button class="btn btn-primary" @click="closeForm3">Okay</button>
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
      searchText: "",
      messageContent: "",
      selectedUser: {
        id: null,
        username: "",
        office: "",
        phone_no: "",
        email: "",
        password: "",
      },
      sendingInProgress: false,
      dueDate: "",
      lastInsertedDate: null,
      currentTime: null,
      editUserModal: null,
      sendSmsModal: null,
      successModal: null,
    };
  },

  created() {
    this.getUsersInfo();
    this.getDueDate();
    setInterval(() => {
      this.getDueDate();
      this.checkTime();
    }, 600);
  },

  methods: {
    async SetDate() {
      try {
        const response = await axios.post("/insertDue", {
          dueDate: this.dueDate,
        });

        if (response.status === 200) {
          console.log("Successfully set date");
        } else {
          console.log("Unsuccessfully set date");
        }
      } catch (e) {
        console.log(e);
      }
    },

    async getDueDate() {
      try {
        const response = await axios.get("/selectDue");
        this.lastInsertedDate = response.data.date;
      } catch (e) {
        console.log(e);
      }
    },

    checkTime() {
      const currentDate = new Date();
      const lastInsertedDate = new Date(this.lastInsertedDate);
      const formattedCurrentDate = `${currentDate.getFullYear()}-${String(
        currentDate.getMonth() + 1
      ).padStart(2, "0")}-${String(currentDate.getDate()).padStart(
        2,
        "0"
      )} ${String(currentDate.getHours()).padStart(2, "0")}:${String(
        currentDate.getMinutes()
      ).padStart(2, "0")}:${String(currentDate.getSeconds()).padStart(2, "0")}`;

      if (formattedCurrentDate === this.lastInsertedDate) {
        this.changeAllUserStatus();
        this.dueDate = "";
      }
    },

    async getUsersInfo() {
      try {
        const UsersInfo = await axios.get("getUsersInfo");
        this.UsersInfo = UsersInfo.data;
      } catch (e) {
        console.log(e);
      }
    },

    openForm(UsersInfo) {
      this.selectedUser = { ...UsersInfo };
      this.editUserModal = new Modal(document.getElementById("editUserModal"));
      this.editUserModal.show();
    },

    openForm2(UsersInfo) {
      this.selectedUser = { ...UsersInfo };
      this.sendSmsModal = new Modal(document.getElementById("sendSmsModal"));
      this.sendSmsModal.show();
    },

    async saveUser() {
      try {
        const response = await axios.post("/api/saveUser", this.selectedUser);

        if (response.status === 200) {
          const responseData = response.data;

          if (responseData && responseData.success) {
            console.log(responseData.message);
            this.editUserModal.hide();
            this.getUsersInfo();
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

    async changeAllUserStatus() {
      try {
        const response = await axios.post("/changeAllUserStatus");
        if (response.status === 200) {
          const responseData = response.data;

          if (responseData && responseData.success) {
            this.getUsersInfo();
          } else {
            console.error("Status change failed:", responseData.message);
          }
        } else {
          console.error(`Unexpected response status: ${response.status}`);
        }
      } catch (error) {
        console.error("Error changing all user status:", error);
      }
    },

    async toggleUserStatus(userInfo) {
      try {
        const response = await axios.post("/toggleUserStatus/", {
          userId: userInfo,
        });

        if (response.status === 200) {
          const responseData = response.data;

          if (responseData && responseData.success) {
            this.getUsersInfo();
          } else {
            console.error("Status change failed:", responseData.message);
          }
        } else {
          console.error(`Unexpected response status: ${response.status}`);
        }
      } catch (error) {
        console.error("Error changing user status:", error);
      }
    },

    findData() {
      const searchText = document.querySelector("#searchText").value;

      if (searchText === "") {
        this.getUsersInfo();
        return;
      }
      this.UsersInfo = this.UsersInfo.filter((item) => {
        return item.username === searchText;
      });
    },

    async sendSms() {
      try {
        const { phone_no } = this.selectedUser;
        const messageContent = this.messageContent;

        this.sendingInProgress = true;
        const response = await axios.post("/sendSMSToUser", {
          recipient: phone_no,
          message: messageContent,
        });

        if (response.status === 200 && response.data.success) {
          console.log("SMS sent successfully.");
          this.sendSmsModal.hide();
          this.messageContent = "";
          this.showSuccessModal();
        } else {
          console.error("Failed to send SMS.");
        }
      } catch (error) {
        console.error("Error sending SMS:", error);
      } finally {
        this.sendingInProgress = false;
      }
    },

    showSuccessModal() {
      this.successModal = new Modal(document.getElementById("successModal"));
      this.successModal.show();
    },

    closeForm3() {
      if (this.successModal) {
        this.successModal.hide();
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
</style>
