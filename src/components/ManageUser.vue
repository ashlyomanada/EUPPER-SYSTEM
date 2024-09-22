<template>
  <div class="table-data">
    <div class="order">
      <div class="head d-flex flex-column">
        <h3>List of Users</h3>
        <div class="manageItems d-flex justify-content-between"></div>
        <div class="manageItems d-flex justify-content-between">
          <div class="list-items d-flex gap-2">
            <button class="find px-2" @click="openDueDate">Set Closing</button>
          </div>
          <button class="find" @click="changeAllUserStatus">
            <i class="fa-solid fa-power-off"></i>
            Change All User Status
          </button>
          <div class="d-flex gap-2">
            <input
              v-model="searchText"
              type="text"
              class="year"
              id="searchText"
              @change="getUsersInfo"
              required
            />
            <button
              class="find d-flex align-items-center px-4"
              @click="findData"
            >
              <i class="bx bx-search"></i>Find
            </button>
          </div>
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
          <tr v-for="UserInfos in UsersInfo" :key="UserInfos.user_id">
            <td>{{ UserInfos.username }}</td>
            <td>{{ UserInfos.office }}</td>
            <td>{{ UserInfos.phone_no }}</td>
            <td>{{ UserInfos.email }}</td>
            <td>{{ UserInfos.status }}</td>
            <td class="td-btn">
              <button
                class="users-btn"
                @click="toggleUserStatus(UserInfos.user_id)"
              >
                <i
                  class="fa-solid fa-power-off fa-lg"
                  :class="{
                    'green-btn': UserInfos.status === 'Enable',
                    'red-btn': UserInfos.status === 'Disable',
                  }"
                ></i>
              </button>
            </td>
            <td class="td-btn">
              <button class="pen-btn" @click="openForm(UserInfos)">
                <i class="fa-solid fa-pen fa-lg"></i>
              </button>
            </td>
            <td class="td-btn">
              <button class="users-btn" @click="openForm2(UserInfos)">
                <i class="fa-solid fa-phone fa-lg"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <h5 v-if="dataFetched" style="text-align: center">No Ratings Found</h5>
    </div>
  </div>

  <div class="alert-container">
    <v-alert v-if="alertMessage" :type="errorType" class="error-message">{{
      alertMessage
    }}</v-alert>
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

  <!-- Bootstrap Modal for setting due -->

  <div
    class="modal fade"
    id="dueModal"
    tabindex="-1"
    aria-labelledby="editProfileModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div
        class="modal-content"
        style="background: var(--light); color: var(--dark)"
      >
        <div class="modal-header">
          <h5 class="modal-title" id="editProfileModalLabel">Select Duedate</h5>
        </div>
        <div
          class="modal-body d-flex justify-content-center flex-column align-items-center"
        >
          <p>Current due date: {{ this.lastInsertedDate }}</p>
          <input
            type="datetime-local"
            v-model="dueDate"
            style="background: var(--light); color: var(--dark)"
          />
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-primary"
            @click.prevent="SetDate"
          >
            Save DueDate
          </button>
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap Modal for maximum  rate -->

  <!-- <div
    class="modal fade"
    id="maximumRateModal"
    tabindex="-1"
    aria-labelledby="editProfileModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div
        class="modal-content"
        style="background: var(--light); color: var(--dark)"
      >
        <div class="modal-header">
          <h5 class="modal-title" id="editProfileModalLabel">
            Set Maximum Users Rate
          </h5>
        </div>
        <div
          class="modal-body d-flex justify-content-center align-items-center"
        >
          <table class="table">
            <thead>
              <tr>
                <th scope="col">Username</th>
                <th scope="col">Office</th>
                <th scope="col">Max Rate</th>
                <th scope="col">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="UsersInfos in UsersInfo" :key="UsersInfos.user_id">
                <td>{{ UsersInfos.username }}</td>
                <td>{{ UsersInfos.office }}</td>
                <td>
                  {{ UsersInfos.maxRate }}
                </td>
                <td><button class="btn btn-danger">Edit MaxRate</button></td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-primary"
            @click.prevent="setMaxRate"
          >
            Save
          </button>
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
            Close
          </button>
        </div>
      </div>
    </div>
  </div> -->
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
      lastInsertedDate: "",
      currentTime: null,
      editUserModal: null,
      sendSmsModal: null,
      successModal: null,
      dataFetched: false,
      alertMessage: "",
      errorType: "",
      checkInterval: null,
      currentDue: "",
    };
  },

  created() {
    this.getUsersInfo();
    this.getDueDate();

    this.checkInterval = setInterval(() => {
      this.getDueDate();
    }, 5000);
  },

  mounted() {
    this.getDueDate();
    this.getUsersInfo();
  },

  methods: {
    async SetDate() {
      try {
        const response = await axios.post("/insertDue", {
          dueDate: this.dueDate,
        });

        if (response.status === 200) {
          console.log("Successfully set date");
          const modalElement = document.getElementById("dueModal");
          const modalInstance =
            Modal.getInstance(modalElement) || new Modal(modalElement);
          modalInstance.hide();
          this.alertMessage = "Successfully set a due date";
          this.errorType = "success";

          setTimeout(() => {
            this.alertMessage = null;
            this.errorType = null;
          }, 5000);
        } else {
          console.log("Unsuccessfully set date");
        }
      } catch (e) {
        console.log(e);
      }
    },

    openDueDate() {
      const modalElement = document.getElementById("dueModal");
      const modalInstance = new Modal(modalElement);
      modalInstance.show();
    },

    async getDueDate() {
      try {
        const date = new Date();

        // Extracting year, month, day, hours, minutes, and seconds
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const day = String(date.getDate()).padStart(2, "0");
        const hours = String(date.getHours()).padStart(2, "0");
        const minutes = String(date.getMinutes()).padStart(2, "0");
        const seconds = String(date.getSeconds()).padStart(2, "0");

        // Format the date to "YYYY-MM-DD HH:mm:ss"
        const formattedDate = `${year}-${month}-${day} ${hours}:${minutes}`;

        const response = await axios.get("/selectDue");
        const respData = response.data.date;

        if (respData) {
          // Ensure that respData is in the correct format
          this.lastInsertedDate = respData;

          const trimmedDateString = this.lastInsertedDate.slice(0, 16);

          // console.log("Last Inserted Date:", trimmedDateString); // Add logging for debugging
          // console.log("Formated Date:", formattedDate);

          // Compare the two formatted dates
          if (formattedDate === trimmedDateString) {
            console.log("Condition met: calling changeAllUserStatus");
            this.changeAllUserStatus(); // Call function only if condition is met
            clearInterval(this.checkInterval);
          } else {
            // console.log("Condition not met: Dates do not match");
          }
        } else {
          console.error("No date returned from the response");
        }
      } catch (e) {
        console.log("Error in getDueDate:", e);
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
          this.alertMessage = "All users status successfully changed.";
          this.errorType = "success";

          setTimeout(() => {
            this.alertMessage = null;
            this.errorType = "success";
          }, 3000);

          if (responseData && responseData.success) {
            this.getUsersInfo();
          } else {
            console.error("Status change failed:", responseData.message);
            this.alertMessage = "Failed to change status";
            this.errorType = "error";
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
        const response = await axios.post("/toggleUserStatus", {
          userId: parseInt(userInfo),
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

    async findData() {
      try {
        const response = await axios.post("/findUsersInfo", {
          Username: this.searchText,
        });

        if (response.status === 200) {
          this.UsersInfo = response.data;
          this.dataFetched = false;
        }

        if (this.UsersInfo.length === 0) {
          this.dataFetched = true;
        }
      } catch (error) {
        console.log(error);
        if (error.response && error.response.status === 400) {
          console.log(error.response);
        }
      }
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
@media screen and (max-width: 768px) {
  .manageItems {
    align-items: center;
  }
}
</style>
