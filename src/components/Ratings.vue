<template>
  <div class="table-data">
    <div class="order">
      <div id="evaluation-ratings">
        <div class="table-options">
          <div class="options-control">
            <div class="w-25 d-flex">
              <h3>User Ratings</h3>
            </div>
            <div class="w-75 d-flex justify-content-end gap-2">
              <label class="d-flex align-items-center" for="month"
                >Select User:</label
              >
              <select class="month" name="month" v-model="selectedUser">
                <option
                  v-for="user in allUsersName"
                  :key="user.user_id"
                  :value="user.user_id"
                >
                  {{ user.username }}
                </option>
              </select>
              <label class="d-flex align-items-center" for="month"
                >Select Table:</label
              >
              <select v-model="selectedTable" class="month" name="month">
                <option value="ppo">PPO CPO LEVEL</option>
                <option value="2">RMFB PMFC LEVEL</option>
                <option value="3">Occidental Mindoro PPO</option>
                <option value="3">Oriental Mindoro PPO</option>
                <option value="3">Marinduque PPO</option>
                <option value="3">Romblon PPO</option>
                <option value="3">Palawan PPO</option>
                <option value="3">Puerto Princesa CPO</option>
              </select>
              <button @click="fetchData" class="find">
                <i class="bx bx-search"></i>Find
              </button>
            </div>
          </div>
        </div>
      </div>
      <table>
        <thead>
          <tr>
            <th>Action</th>
            <th>Month</th>
            <th>Year</th>
            <th v-for="(column, index) in columns" :key="index" class="t-row">
              {{ column.replace(/_/g, " ") }}
            </th>
          </tr>
        </thead>
        <tbody v-if="dataFetched && userRatings.length > 0">
          <tr v-for="(rating, index) in userRatings" :key="index">
            <td>
              <button class="pen-btn" id="penButton">
                <i class="fa-solid fa-pen fa-lg"></i>
              </button>
            </td>
            <td class="t-data">{{ rating.month }}</td>
            <td class="t-data">{{ rating.year }}</td>
            <td
              v-for="(column, colIndex) in columns"
              :key="colIndex"
              class="t-data"
            >
              {{ rating[column] }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <!-- <form
    class="form"
    id="rating-form-edit"
    :style="{ display: formVisible ? 'block' : 'none' }"
  >
    <div class="form-edit">
      <label for="">Office:</label>
      <input
        v-model="selectedRatings.office"
        type="text"
        placeholder=""
        class="input"
      />
      <label for="">do:</label>
      <input
        v-model="selectedRatings.do"
        type="text"
        placeholder=""
        class="input"
      />
      <label for="">didm:</label>
      <input
        v-model="selectedRatings.didm"
        type="text"
        placeholder=" Mindoro"
        class="input"
      />
    </div>
    <div class="form-edit">
      <label for="">di:</label>
      <input
        v-model="selectedRatings.di"
        type="text"
        placeholder=" Mindoro"
        class="input"
      />
      <label for="">dpcr:</label>
      <input
        v-model="selectedRatings.dpcr"
        type="text"
        placeholder=""
        class="input"
      />
      <label for="">dl:</label>
      <input
        v-model="selectedRatings.dl"
        type="text"
        placeholder=""
        class="input"
      />
    </div>
    <div class="form-edit">
      <label for="">dhrdd:</label>
      <input
        v-model="selectedRatings.dhrdd"
        type="text"
        placeholder=""
        class="input"
      />
      <label for="">dprm:</label>
      <input
        v-model="selectedRatings.dprm"
        type="text"
        placeholder=""
        class="input"
      />
      <label for="">dictm:</label>
      <input
        v-model="selectedRatings.dictm"
        type="text"
        placeholder=""
        class="input"
      />
    </div>
    <div class="form-edit">
      <label for="">dpl:</label>
      <input
        v-model="selectedRatings.dpl"
        type="text"
        placeholder=""
        class="input"
      />
      <label for="">dc:</label>
      <input
        v-model="selectedRatings.dc"
        type="text"
        placeholder=""
        class="input"
      />
      <label for="">drd:</label>
      <input
        v-model="selectedRatings.drd"
        type="text"
        placeholder=""
        class="input"
      />
    </div>
    <div class="modal-buttons">
      <button @click.prevent="saveUserRates">Save</button>
      <button @click.prevent="closeForm">Close</button>
    </div>
  </form> -->
</template>

<script>
import axios from "axios";
export default {
  data() {
    return {
      userRatings: [],
      columns: [],
      selectedTable: "",
      selectedUser: 54,
      allUsersName: "",
      dataFetched: false,
    };
  },

  created() {
    this.fetchColumns();
    this.getUsername();
    this.fetchData();
  },

  methods: {
    async fetchColumns() {
      try {
        const response = await axios.get("/getColumnNamePPO");
        this.columns = response.data.filter(
          (column) => !["id", "userid", "month", "year"].includes(column)
        );
      } catch (error) {
        console.error("Error fetching column names:", error);
      }
    },

    async getUsername() {
      try {
        const response = await axios.get("/getAllUsersName");
        this.allUsersName = response.data;
        //console.log(response.data);
      } catch (e) {
        console.log(e);
      }
    },

    fetchData() {
      const userId = this.selectedUser;
      console.log(userId);
      if (userId) {
        axios
          .get(`/viewUserPPORates/${userId}`)
          .then((response) => {
            this.userRatings = response.data;
            this.dataFetched = true;
          })
          .catch((error) => {
            console.error("Error fetching user data:", error);
          });
      }
    },
  },
};
</script>

<style>
#rating-form-edit {
  position: absolute;
  width: 80%;
  top: 15%;
  left: 10%;
  display: none;
}
.form-edit {
  display: flex;
  gap: 2rem;
  align-items: center;
}
.evaluation-ratings {
  display: flex;
  align-items: center;
  grid-gap: 16px;
  margin-bottom: 24px;
  flex-direction: column;
}
.table-options {
  width: 100%;
  display: flex;
  align-items: center;
  padding: 1rem 0rem;
}
.options-control {
  width: 100%;
  display: flex;
  gap: 1rem;
}
</style>
