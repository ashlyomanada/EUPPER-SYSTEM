<template>
  <div class="table-data">
    <div class="order">
      <div class="head">
        <!-- <div class="head-options">
          <div style="text-align: center">
            <h3>Unit Performance Evaluation Rating</h3>
            <h4>Add New User</h4>
          </div>
        </div> -->
      </div>
      <div class="newUserContainer">
        <form class="formUser" @submit.prevent="submitForm">
          <p class="title">Register New User</p>
          <div class="d-flex w-100 gap-3">
            <div class="inputContainer">
              <label>
                <input
                  required=""
                  placeholder=""
                  type="text"
                  class="inputs"
                  v-model="formData.username"
                />
                <span>Username</span>
              </label>

              <label>
                <input
                  required=""
                  placeholder=""
                  type="text"
                  class="inputs"
                  v-model="formData.office"
                />
                <span>Office</span>
              </label>

              <label>
                <input
                  required=""
                  placeholder=""
                  type="email"
                  class="inputs"
                  v-model="formData.email"
                />
                <span>Email</span>
              </label>
            </div>

            <div class="inputContainer">
              <label>
                <input
                  required=""
                  placeholder=""
                  type="password"
                  class="inputs"
                  v-model="formData.password"
                />
                <span>Password</span>
              </label>

              <label>
                <input
                  required=""
                  placeholder=""
                  type="number"
                  class="inputs"
                  v-model="formData.phone_no"
                />
                <span>Phone Number</span>
              </label>

              <label>
                <select class="inputs" v-model="formData.officeType" required>
                  <option value="Operational Office">Operational Office</option>
                  <option value="Administrative Office">
                    Administrative Office
                  </option>
                </select>
                <span>Office Type</span>
              </label>
            </div>
          </div>

          <label>
            <input
              type="file"
              placeholder="Profile picture"
              @change="handleFileChange"
              class="inputs"
              required
              accept="image/*"
              ref="fileInput2"
            />
          </label>

          <!-- Add this input to your form -->

          <button type="submit" class="submit" style="color: white">
            Register
          </button>
        </form>
      </div>
    </div>

    <div class="alert-container">
      <v-alert v-if="alertMessage" :type="errorType" class="error-message">{{
        alertMessage
      }}</v-alert>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      formData: {
        username: "",
        office: "",
        email: "",
        password: "",
        phone_no: "",
        officeType: "",
        file: null,
      },
      alertMessage: null,
      errorType: null,
    };
  },
  methods: {
    submitForm() {
      const formData = new FormData();

      // Append non-file fields
      for (const key in this.formData) {
        formData.append(key, this.formData[key]);
      }

      // // Logging to check the FormData
      // for (const pair of formData.entries()) {
      //   console.log(pair[0], pair[1]);
      // }

      // Use axios or fetch to send the form data to your CodeIgniter backend
      axios
        .post("/upload", formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        })
        .then((response) => {
          // console.log(response.data);
          this.formData = {
            username: "",
            office: "",
            email: "",
            password: "",
            phone_no: "",
            officeType: "",
            file: null, // Reset the file input
          };
          this.$refs.fileInput2.value = "";
          this.alertMessage =
            "Successfully registered new account for new User.";
          this.errorType = "success";
        })
        .catch((error) => {
          if (error.response.status === 400) {
            this.alertMessage =
              "The email you entered is already in used or the file you uploaded to large.";
            this.errorType = "error";
          } else if (error.response.status === 500) {
            this.alertMessage = "Server error, Please try again later";
            this.errorType = "error";
          } else {
            this.alertMessage =
              "Please check internet connection and try again later";
            this.errorType = "error";
          }
        });
      setTimeout(() => {
        this.errorType = null;
        this.alertMessage = null;
      }, 5000);
    },
    handleFileChange(event) {
      this.formData.file = event.target.files[0];
    },
  },
};
</script>

<style scoped>
.newUserContainer {
  display: flex;
  height: 90%;
  width: 100%;
}
.inputContainer {
  display: flex;
  gap: 0.5rem;
  flex-direction: column;
  width: 50%;
}
.column {
  display: flex;
  gap: 0.5rem;
  width: 50%;
}
.register-container {
  height: 100vh;
  width: 100vw;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #0c0c1e;
}
.register {
  height: 85vh;
  width: 65vw;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #fff;
  border-radius: 1.5rem;
  box-shadow: 0 30px 30px -30px rgba(27, 26, 26, 0.315);
}
.picture-container {
  height: 100%;
  width: 50%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 3rem;
}
.name-container {
  text-align: center;
}
.loginLogo {
  height: 200px;
}
.formUser {
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding: 20px;
  border-radius: 20px;
  position: relative;
  width: 100vw;
  background-color: var(--light);
}

.title {
  font-size: 28px;
  color: royalblue;
  font-weight: 600;
  letter-spacing: -1px;
  position: relative;
  display: flex;
  align-items: center;
  padding-left: 30px;
}

.title::before,
.title::after {
  position: absolute;
  content: "";
  height: 16px;
  width: 16px;
  border-radius: 50%;
  left: 0px;
  background-color: royalblue;
}

.title::before {
  width: 18px;
  height: 18px;
  background-color: royalblue;
}

.title::after {
  width: 18px;
  height: 18px;
  animation: pulse 1s linear infinite;
}

.message,
.signin {
  color: black;
  font-size: 14px;
}

.signin {
  text-align: center;
}

.signin a {
  color: royalblue;
}

.signin a:hover {
  text-decoration: underline royalblue;
}

.flex {
  display: flex;
  width: 100%;
  gap: 6px;
}

.formUser label {
  position: relative;
  color: var(--light);
}

.inputs {
  color: var(--dark);
}

.formUser label .inputs {
  width: 100%;
  padding: 10px 10px 20px 10px;
  outline: 0;
  border: 1px solid var(--dark);
  border-radius: 10px;
}

.formUser label .inputs + span {
  position: absolute;
  left: 10px;
  top: 15px;
  color: var(--dark);
  font-size: 0.9em;
  cursor: text;
  transition: 0.3s ease;
}

.formUser label .inputs:placeholder-shown + span {
  top: 15px;
  font-size: 0.9em;
}

.formUser label .inputs:focus + span,
.formUser label .inputs:valid + span {
  top: 30px;
  font-size: 0.7em;
  font-weight: 600;
}

.formUser label .inputs:valid + span {
  color: green;
}

.submit {
  border: none;
  outline: none;
  background-color: royalblue;
  padding: 10px;
  border-radius: 10px;
  color: #fff;
  font-size: 16px;
  transform: 0.3s ease;
}

.submit:hover {
  background-color: rgb(56, 90, 194);
}

@keyframes pulse {
  from {
    transform: scale(0.9);
    opacity: 1;
  }

  to {
    transform: scale(1.8);
    opacity: 0;
  }
}
@media screen and (max-width: 768px) {
  .register {
    width: 90vw;
  }
}
@media screen and (max-width: 600px) {
  .picture-container {
    display: none;
  }
  .formUser {
    width: 100vw;
  }
  .register {
    background-color: transparent;
  }

  .newUserContainer {
    width: 100vw;
  }
}
</style>
