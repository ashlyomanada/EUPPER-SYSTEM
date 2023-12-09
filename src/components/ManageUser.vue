<template>
  <div class="table-data">
    <div class="order">
      <div class="head">
        <h3>List of Users</h3>
        <button class="find" @click="changeAllUserStatus">
          <i class="fa-solid fa-power-off"></i>
          Change All User Status
        </button>
        <div>
          <button class="find" @click="filterUsers">
            <i class="bx bx-search"></i>Find
          </button>
          <input v-model="searchText" type="text" class="year" />
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
          <tr v-for="UsersInfo in UsersInfo" :key="UsersInfo.id">
            <td>{{ UsersInfo.username }}</td>
            <td>{{ UsersInfo.office }}</td>
            <td>{{ UsersInfo.phone_no }}</td>
            <td>{{ UsersInfo.email }}</td>
            <td>{{ UsersInfo.status }}</td>
            <td class="td-btn">
              <button class="users-btn" @click="toggleUserStatus(UsersInfo.id)">
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
              <button class="users-btn">
                <i class="fa-solid fa-phone fa-lg"></i>
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
      v-model="selectedUser.username"
      type="text"
      placeholder="Username"
      class="input"
    />
    <input
      v-model="selectedUser.office"
      type="text"
      placeholder="Office"
      class="input"
    />
    <input
      v-model="selectedUser.email"
      type="text"
      placeholder="Email"
      class="input"
    />
    <input
      v-model="selectedUser.phone_no"
      type="text"
      placeholder="Phone No."
      class="input"
    />
    <div class="modal-buttons">
      <button @click.prevent="saveUser">Save</button>
      <button @click.prevent="closeForm">Close</button>
    </div>
  </form>
</template>

<script>
import axios from "axios";
export default {
  data() {
    return {
      UsersInfo: [],
      searchText: "",
      formVisible: false,
      selectedUser: {
        id: null,
        username: "",
        office: "",
        phone_no: "",
        email: "",
      },
    };
  },

  created() {
    this.getUsersInfo();
  },
  methods: {
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
      this.formVisible = true;
    },

    async saveUser() {
      try {
        const response = await axios.post("/api/saveUser", this.selectedUser);

        if (response.status === 200) {
          const responseData = response.data;

          if (responseData && responseData.success) {
            console.log(responseData.message);
            this.formVisible = false;
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

    closeForm() {
      this.formVisible = false;
    },

    async changeAllUserStatus() {
      try {
        const response = await axios.post("/changeAllUserStatus");
        if (response.status === 200) {
          const responseData = response.data;

          if (responseData && responseData.success) {
            console.log(responseData.message);
            this.getUsersInfo(); // Refresh the user list after the status change
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
      console.log("userInfo:", userInfo);

      try {
        const response = await axios.post("/toggleUserStatus/", {
          userId: userInfo,
        });
        console.log("Server response:", response);
        if (response.status === 200) {
          const responseData = response.data;

          if (responseData && responseData.success) {
            console.log(responseData.message);
            this.getUsersInfo(); // Refresh the user list after the status change
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

    async filterUsers() {
      try {
        const response = await axios.get("/filterUsers/" + this.searchText);
        //console.log(this.searchText);
        if (response.status === 200) {
          this.UsersInfo = response.data;
          console.log(this.UsersInfo);
        } else {
          console.error("Error filtering users:", response.data);
        }
      } catch (error) {
        console.error("Error filtering users:", error);
      }
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
