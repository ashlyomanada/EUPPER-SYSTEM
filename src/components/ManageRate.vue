<template>
  <div class="table-data">
    <div class="order">
      <div class="head d-flex flex-column">
        <h3>List of Users Max Rate</h3>
      </div>
      <table>
        <thead>
          <tr>
            <th>Username</th>
            <th>Office</th>
            <th>Max Rate</th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="UserInfos in UsersInfo" :key="UserInfos.user_id">
            <td>{{ UserInfos.username }}</td>
            <td>{{ UserInfos.office }}</td>
            <td>{{ UserInfos.maxRate }}</td>

            <td class="td-btn">
              <button class="btn btn-primary" @click="openMaxRate(UserInfos)">
                <i class="fa-solid fa-pen fa-lg"></i>
                Edit Max Rate
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <h5 v-if="dataFetched" style="text-align: center">No Ratings Found</h5>
    </div>
  </div>

  <div
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
        <div class="modal-body d-flex justify-content-center gap-2 flex-column">
          <div class="mb-3 d-flex align-items-start flex-column">
            <input
              type="hidden"
              class="form-control"
              readonly
              style="cursor: pointer"
              v-model="userId"
            />
          </div>
          <div class="mb-3 d-flex align-items-start flex-column">
            <label for="exampleFormControlInput1" class="form-label"
              >Username</label
            >
            <input
              type="text"
              class="form-control"
              readonly
              style="cursor: pointer"
              v-model="username"
            />
          </div>
          <div class="mb-3 d-flex align-items-start flex-column">
            <label for="exampleFormControlInput1" class="form-label"
              >Office</label
            >
            <input
              type="text"
              class="form-control"
              readonly
              style="cursor: pointer"
              v-model="office"
            />
          </div>
          <div class="mb-3 d-flex align-items-start flex-column">
            <label for="exampleFormControlInput1" class="form-label"
              >Max Rate</label
            >
            <input type="number" class="form-control" v-model="userMaxRate" />
          </div>
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
  </div>

  <div class="alert-container">
    <v-alert v-if="alertMessage" :type="errorType" class="error-message">{{
      alertMessage
    }}</v-alert>
  </div>
</template>

<script>
import axios from "axios";
import { Modal } from "bootstrap";

export default {
  data() {
    return {
      UsersInfo: [],
      userId: 0,
      username: "",
      office: "",
      userMaxRate: 0,
      alertMessage: "",
      errorType: "",
      dataFetched: false,
      maxRateModal: null,
    };
  },

  mounted() {
    this.getUsersInfo();
    const maximumRateModalElement = document.getElementById("maximumRateModal");
    this.maxRateModal = new Modal(maximumRateModalElement);
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

    openMaxRate(UsersInfo) {
      const maximumRateModalElement =
        document.getElementById("maximumRateModal");
      const maxRateModal = new Modal(maximumRateModalElement);
      maxRateModal.show();

      this.userId = UsersInfo.user_id;
      this.username = UsersInfo.username;
      this.office = UsersInfo.office;
      this.userMaxRate = UsersInfo.maxRate;
    },

    async setMaxRate() {
      try {
        const response = await axios.post("/saveMaxRate", {
          UserId: this.userId,
          Username: this.username,
          Office: this.office,
          MaxRate: parseFloat(this.userMaxRate),
        });

        if (response.status === 200) {
          this.alertMessage = "Successfully Set Max Rate";
          this.errorType = "success";
          this.getUsersInfo();

          setTimeout(() => {
            this.alertMessage = null;
            this.errorType = null;
            this.maxRateModal.hide();
          }, 3000);
        }
      } catch (error) {
        if (error.response && error.response.status === 401) {
          this.alertMessage = "Please input a valid Max Rate";
          this.errorType = "error";
          this.maxRateModal.hide();
        } else if (error.response && error.response.status === 500) {
          this.alertMessage = "Server error, please try again later";
          this.errorType = "error";
          this.maxRateModal.hide();
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
