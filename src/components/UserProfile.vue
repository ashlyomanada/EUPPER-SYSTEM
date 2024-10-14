<template>
  <div class="main-profile-container">
    <div class="profile-container">
      <div class="cover-container">
        <img src="./img/cover.png" alt="" class="cover-photo" />
      </div>
      <div class="img-container">
        <div class="img-left">
          <div class="editImageContainer">
            <img :src="`${baseURL}${profilePic}`" alt="" id="profile-pic" />
            <i
              style="
                position: absolute;
                bottom: 10%;
                right: 10%;
                font-size: 2rem;
                color: green;
              "
              class="fa-solid fa-pencil fa-lg"
              @click="editProfile"
            ></i>
          </div>
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
          <i class="fa-solid fa-briefcase fa-sm"></i>Office:
          {{ officeLocation }}
        </p>
        <p>
          <i class="fa-solid fa-phone fa-sm"></i>Phone No. {{ phoneNumber }}
        </p>
        <p><i class="fa-solid fa-envelope fa-sm"></i>Email: {{ email }}</p>
      </div>
      <div class="alert-container">
        <v-alert v-if="successMessage" type="success" class="error-message">
          {{ successMessage }}
        </v-alert>
        <v-alert v-if="errorMessage" type="error" class="error-message">
          {{ errorMessage }}
        </v-alert>
      </div>
    </div>
  </div>

  <!-- Bootstrap Modal for Editing User Details -->
  <div
    class="modal fade"
    id="editProfileModal"
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
          <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
        </div>
        <div class="modal-body text-start">
          <form>
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
                required
              />
            </div>
            <div class="mb-3">
              <label for="office" class="form-label text-start">Office</label>
              <input
                style="background: var(--light); color: var(--dark)"
                v-model="selectedUser.office"
                type="text"
                class="form-control"
                id="office"
                placeholder="Office"
                required
              />
            </div>
            <div class="mb-3">
              <label for="email" class="form-label text-start">Email</label>
              <input
                style="background-color: var (--light); color: var(--dark)"
                v-model="selectedUser.email"
                type="email"
                class="form-control"
                id="email"
                placeholder="Email"
                required
              />
            </div>
            <div class="mb-3">
              <label for="phone" class="form-label text-start"
                >Phone Number</label
              >
              <input
                style="background-color: var (--light); color: var(--dark)"
                v-model="selectedUser.phone_no"
                type="text"
                class="form-control"
                id="phone"
                placeholder="Phone No."
                required
              />
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-primary"
            @click.prevent="saveUser"
          >
            Save changes
          </button>
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap Modal for Editing Profile Picture -->
  <div
    class="modal fade"
    id="editProfilePicModal"
    tabindex="-1"
    aria-labelledby="editProfilePicModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div
        class="modal-content"
        style="background: var(--light); color: var(--dark)"
      >
        <div class="modal-header">
          <h5 class="modal-title" id="editProfilePicModalLabel">
            Edit Profile Picture
          </h5>
        </div>
        <div class="modal-body">
          <div class="d-flex flex-column align-items-center gap-5">
            <label class="labels" for="">Profile Picture</label>
            <img
              v-if="profilePicUrl"
              style="height: 10rem; border-radius: 50%"
              :src="profilePicUrl"
            />
            <img
              v-else
              style="height: 10rem; border-radius: 50%"
              :src="`${baseURL}${profilePic}`"
            />
            <!-- <input
              id="editProfileInput"
              type="file"
              accept="image/*"
              style="background: var(--light); color: var(--dark)"
              @change="onFileChange"
              required
            /> -->

            <div class="input-group mb-3">
              <label class="input-group-text" for="inputGroupFile01"
                >Upload</label
              >
              <input
                id="editProfileInput"
                type="file"
                accept="image/*"
                style="background: var(--light); color: var(--dark)"
                @change="onFileChange"
                required
                class="form-control"
              />
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-primary"
            @click.prevent="saveProfilePic"
          >
            Save changes
          </button>
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { Modal } from "bootstrap"; // Import Modal from Bootstrap

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
      formVisible2: false,
      profilePicUrl: "",
      selectedUser: {
        user_id: null,
        username: "",
        office: "",
        phone_no: "",
        email: "",
      },
      file: null,
      errorMessage: "",
      successMessage: "",
      baseURL: axios.defaults.baseURL,
    };
  },
  mounted() {
    this.fetchUserData();
    setTimeout(() => {
      this.errorMessage = null;
    }, 5000);
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

    onFileChange(event) {
      const file = event.target.files[0];
      if (file) {
        this.file = file;
        const reader = new FileReader();
        reader.onload = (e) => {
          this.profilePicUrl = e.target.result; // Set the image URL to the result of FileReader
        };
        reader.readAsDataURL(file);
      }
    },

    openForm() {
      // Show Bootstrap modal
      const modal = new Modal(document.getElementById("editProfileModal"));
      modal.show();

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
      // Hide Bootstrap modal
      const modal = Modal.getInstance(
        document.getElementById("editProfileModal")
      );
      modal.hide();
    },

    closeForm2() {
      // Hide Bootstrap modal
      const modal = Modal.getInstance(
        document.getElementById("editProfilePicModal")
      );
      modal.hide();
      this.profilePicUrl = "";
      const editProfileInput = document.getElementById("editProfileInput");
      editProfileInput.value = "";
    },

    editProfile() {
      // Show Bootstrap modal
      const modal = new Modal(document.getElementById("editProfilePicModal"));
      modal.show();
    },

    async saveUser() {
      try {
        const response = await axios.post("/api/saveUser", this.selectedUser);

        if (response.status === 200) {
          const responseData = response.data;

          if (responseData && responseData.success) {
            this.closeForm();
            this.fetchUserData();
            this.successMessage = "Successfully Updated User Details";
            setTimeout(() => {
              this.successMessage = null;
            }, 5000);
          } else {
            console.error("Save failed:", responseData.message);
            this.errorMessage = responseData.message;
            setTimeout(() => {
              this.errorMessage = null;
            }, 5000);
          }
        } else {
          console.error(`Unexpected response status: ${response.status}`);
        }
      } catch (error) {
        console.error("Error saving user:", error);
      }
    },

    async saveProfilePic() {
      try {
        if (!this.file) {
          this.errorMessage = "Please select a file.";
          return;
        }

        const storedUserId = sessionStorage.getItem("id");
        if (!storedUserId) {
          this.errorMessage = "User ID not found.";
          return;
        }

        const formData = new FormData();
        formData.append("file", this.file);
        formData.append("userId", storedUserId);

        const response = await axios.post("/uploadProfile", formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        });

        if (response.status === 200) {
          this.profilePic = response.data.profilePicPath;
          this.profilePicUrl = `${this.baseURL}${response.data.profilePicPath}`;
          this.successMessage = "Profile picture uploaded successfully!";
          setTimeout(() => {
            this.successMessage = null;
          }, 5000);
          this.closeForm2();
        }
      } catch (error) {
        console.error("Error uploading profile picture:", error);
        this.errorMessage = "Failed to upload profile picture.";
        setTimeout(() => {
          this.errorMessage = null;
        }, 5000);
      }
    },
  },
};
</script>

<style>
#phone,
#email {
  background-color: var(--light);
}
.alert-container {
  position: absolute;
  top: 0;
  right: 0;
  z-index: 100;
}
.labels {
  color: var(--dark);
}
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
.main-profile-container {
  border-radius: 20px;
  background: var(--light);
  padding: 24px;
  overflow-x: auto;
}

#modal-form2 {
  position: absolute;
  width: 50%;
  top: 25%;
  left: 37%;
  display: none;
  z-index: 2;
}

#modal-form3 {
  position: absolute;
  width: 30%;
  top: 25%;
  left: 45%;
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
  overflow-x: unset;
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
  margin-top: 14rem;
  z-index: 1;
}
.img-left {
  display: flex;
  height: 100%;
  width: 75%;
  align-items: center;
  gap: 1rem;
  position: relative;
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
  background-color: white;
  border-radius: 50%;
  height: 160px;
}
.profile-description-container {
  height: 38%;
  width: 100%;
  padding: 1rem;
  line-height: 2rem;
}

.editImageContainer {
  display: flex;
  position: relative;
}

@media screen and (max-width: 768px) {
  .cover-container {
    align-items: flex-start;
  }
  .img-container {
    margin-top: 5rem;
  }
  #profile-pic {
    height: 200px;
  }
  .cover-photo {
    height: 50%;
  }
  #modal-form2 {
    left: 30%;
  }
}

@media screen and (max-width: 600px) {
  .main-profile-container {
    padding: 0;
  }
  .img-container {
    flex-direction: column;
    justify-content: center;
  }
  .img-left {
    flex-direction: column;
    width: 100%;
    gap: 0.5rem;
    justify-content: center;
  }
  .img-right {
    height: 15%;
    width: 100%;
    align-items: center;
    justify-content: center;
  }
  .cover-container {
    justify-content: center;
  }
  .profile-container {
    align-items: center;
  }
  .profile-description-container {
    display: flex;
    justify-content: center;
    flex-direction: column;
    text-align: center;
  }

  #profile-pic {
    height: 150px;
  }
  #modal-form2 {
    width: 70%;
    left: 23%;
  }
  .cover-photo {
    height: 40%;
  }
}
</style>
