<template>
  <div class="table-data">
    <div class="order">
      <div class="head">
        <h3>Announcement To Selected Users</h3>
      </div>
      <form class="form" id="form" @submit.prevent="sendSms">
        <label for="userSelection">To:</label>

        <!-- All Users Checkbox -->
        <div class="btn-checkbox">
          <input
            type="checkbox"
            id="all-users"
            v-model="selectAll"
            hidden
            @change="toggleAllUsers"
          />
          <label class="btn btn-outline-primary" for="all-users"
            >All Users</label
          >
        </div>

        <!-- Individual Checkboxes -->
        <div
          class="btn-group"
          role="group"
          aria-label="Basic checkbox toggle button group"
        >
          <div v-for="user in users" :key="user.user_id" class="btn-checkbox">
            <input
              type="checkbox"
              class="btn-check"
              :id="'user-' + user.user_id"
              :value="user.user_id"
              v-model="selectedUsers"
              :disabled="selectAll"
            />
            <label
              class="btn btn-outline-primary"
              :for="'user-' + user.user_id"
            >
              {{ user.office }}
            </label>
          </div>
        </div>
        <span class="error" v-if="errorMessage">{{ errorMessage }}</span>

        <label for="">From:</label>
        <input
          type="text"
          placeholder="PRO MIMAROPA Announcement"
          class="input"
          value="PRO MIMAROPA Announcement"
          readonly
        />

        <textarea
          placeholder="Type message"
          v-model="messageContent"
        ></textarea>

        <button class="btn btn-primary" type="submit">
          <i class="fa-solid fa-paper-plane"></i> Send
        </button>
      </form>
    </div>

    <div class="modalBg" v-if="formVisible">
      <div class="alertBox">
        <img class="checkImg" src="./img/check2.gif" alt="" />
        <h1 class="alertContent">Successfully Sent</h1>
        <button class="btn btn-primary" @click="closeForm">Okay</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      messageContent: "",
      selectedUsers: [],
      selectAll: false, // Flag for "All Users"
      formVisible: false,
      users: [], // List of users fetched from the backend
      errorMessage: "", // Error message for validation
    };
  },
  mounted() {
    this.fetchUsers();
  },
  methods: {
    async fetchUsers() {
      try {
        const response = await axios.get("/getUsers"); // Fetch users
        this.users = response.data;
      } catch (error) {
        console.error("Error fetching users:", error);
      }
    },
    toggleAllUsers() {
      if (this.selectAll) {
        // Select all users
        this.selectedUsers = this.users.map((user) => user.user_id);
      } else {
        // Deselect all users
        this.selectedUsers = [];
      }
    },
    async sendSms() {
      try {
        // Validation: Ensure at least one checkbox is selected
        if (!this.selectAll && this.selectedUsers.length === 0) {
          this.errorMessage = "Please select at least one user.";
          return;
        } else {
          this.errorMessage = "";
        }

        const messageContent = this.messageContent;
        const selectedUsers =
          this.selectAll && this.users.length > 0
            ? this.users.map((user) => user.user_id) // If "All Users" is selected, send all user IDs
            : this.selectedUsers;

        const response = await axios.post("/sendSMSToSelectedUsers", {
          message: messageContent,
          users: selectedUsers,
        });

        if (response.status === 200) {
          console.log("SMS sent successfully.");
          this.formVisible = true;
          this.messageContent = "";
          this.selectedUsers = [];
          this.selectAll = false;
          setTimeout(() => {
            this.formVisible = false;
          }, 5000);
        } else {
          console.error("Failed to send SMS.");
        }
      } catch (error) {
        console.error("Error sending SMS:", error);
      }
    },
    closeForm() {
      this.formVisible = false;
    },
  },
};
</script>

<style>
.form {
  position: relative;
  display: flex;
  align-items: flex-start;
  flex-direction: column;
  gap: 10px;
  width: 100%;
  background: var(--light);
  padding: 20px;
  border-radius: 10px;
}

.form .title {
  color: royalblue;
  font-size: 30px;
  font-weight: 600;
  letter-spacing: -1px;
  line-height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.form input {
  outline: 0;
  border: 1px solid rgb(219, 213, 213);
  padding: 8px 14px;
  border-radius: 8px;
  width: 100%;
  height: 50px;
  color: var(--dark);
}

.form textarea {
  border-radius: 8px;
  height: 100px;
  width: 100%;
  resize: none;
  outline: 0;
  padding: 8px 14px;
  border: 1px solid rgb(219, 213, 213);
  color: var(--dark);
}

.form button {
  align-self: flex-end;
  padding: 8px;
  outline: 0;
  border: 0;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  background-color: royalblue;
  color: #fff;
  cursor: pointer;
}
@media screen and (max-width: 600px) {
  .form {
    width: 100vw;
  }
}
</style>
