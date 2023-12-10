<template>
  <div class="profile-container">
    <div class="cover-container">
      <img src="./img/cover.png" alt="" srcset="" class="cover-photo" />
    </div>
    <div class="img-container">
      <div class="img-left">
        <img
          :src="`http://localhost:8080/${profilePic}`"
          alt=""
          id="profile-pic"
        />
        <h2 class="name">{{ userName }}</h2>
      </div>
      <div class="img-right">
        <button class="edit-profile-btn" @click.prevent="openForm">
          <i class="fa-solid fa-pencil fa-lg"></i>Edit profile
        </button>
      </div>
    </div>
    <div class="profile-description-container">
      <p>
        <i class="fa-solid fa-briefcase fa-sm"></i>Office: {{ officeLocation }}
      </p>
      <p><i class="fa-solid fa-phone fa-sm"></i>Phone No. {{ phoneNumber }}</p>
      <p><i class="fa-solid fa-envelope fa-sm"></i>Email: {{ email }}</p>
    </div>
  </div>
  <form
    class="form"
    id="modal-form2"
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
      userId: null,
      userName: "",
      profilePic: "",
      officeLocation: "",
      phoneNumber: "",
      email: "",
      formVisible: false,
      selectedUser: {
        user_id: null,
        username: "",
        office: "",
        phone_no: "",
        email: "",
      },
    };
  },
  mounted() {
    this.fetchUserData();
  },
  methods: {
    async fetchUserData() {
      const storedUserId = sessionStorage.getItem("id");

      if (storedUserId) {
        try {
          const response = await axios.get(`/getUserData/${storedUserId}`);

          if (response.status === 200) {
            const userData = response.data;
            this.userId = userData.user_id;
            this.userName = userData.username;
            this.officeLocation = userData.office;
            this.phoneNumber = userData.phone_no;
            this.email = userData.email;
            this.profilePic = userData.image;
          } else {
            console.error(`Unexpected response status: ${response.status}`);
          }
        } catch (error) {
          console.error("Error fetching user data:", error);
        }
      }
    },

    openForm() {
      // When the "Edit profile" button is clicked, set the formVisible to true
      this.formVisible = true;

      // Populate the form fields with the current user's data
      this.selectedUser = {
        user_id: this.userId,
        username: this.userName,
        office: this.officeLocation,
        phone_no: this.phoneNumber,
        email: this.email,
      };
    },
    closeForm() {
      this.formVisible = false;
    },
    async saveUser() {
      try {
        const response = await axios.post("/api/saveUser", this.selectedUser);

        if (response.status === 200) {
          const responseData = response.data;

          if (responseData && responseData.success) {
            //console.log(responseData.message);
            this.formVisible = false;
            this.fetchUserData();
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
  },
};
</script>

<style>
#modal-form2 {
  position: absolute;
  width: 50%;
  top: 25%;
  left: 25%;
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
.profile-container {
  height: 91vh;
  width: 100%;
  display: flex;
  flex-direction: column;
  position: relative;
  color: var(--dark);
}
.cover-container {
  position: absolute;
  display: flex;
  height: 45%;
  width: 100%;
  align-items: flex-end;
}
.cover-photo {
  height: 92%;
  width: 100%;
}
.img-container {
  display: flex;
  height: 38%;
  width: 100%;
  align-items: center;
  padding: 1rem;
  margin-top: 11.5rem;
  z-index: 1;
}
.img-left {
  display: flex;
  height: 100%;
  width: 75%;
  align-items: center;
  gap: 1rem;
}
.img-right {
  display: flex;
  height: 100%;
  width: 25%;
  align-items: center;
  justify-content: center;
}
.edit-profile-btn {
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  background: var(--blue);
  color: white;
}
#profile-pic {
  height: 90%;
  background-color: white;
  border-radius: 50%;
}
.profile-description-container {
  height: 38%;
  width: 100%;
  padding: 1rem;
  line-height: 2rem;
}
</style>
