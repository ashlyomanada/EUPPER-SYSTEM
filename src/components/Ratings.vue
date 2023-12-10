<template>
  <div class="table-data">
    <div class="order">
      <div id="evaluation-ratings">
        <div>
          <h3>Unit Performance Evaluation Rating</h3>
          <h4>R1 - DPRM</h4>
        </div>
        <div class="table-options">
          <div class="options-control">
            Select User:
            <select v-model="selectedUserId" class="month" name="month">
              <option
                v-for="user in users"
                :key="user.user_id"
                :value="user.user_id"
              >
                {{ user.username }}
              </option>
            </select>
            Select Table:
            <select v-model="selectedTable" class="month" name="month">
              <option value="1">PPO CPO LEVEL</option>
              <option value="2">RMFB PMFC LEVEL</option>
              <option value="3">MPS CPS LEVEL</option>
            </select>
            <button @click="findData" class="find">
              <i class="bx bx-search"></i>Find
            </button>
          </div>
        </div>
      </div>
      <table>
        <thead>
          <tr>
            <th>Action</th>
            <th>Office</th>
            <th>Do</th>
            <th>Didm</th>
            <th>Di</th>
            <th>Dpcr</th>
            <th>Dl</th>
            <th>Dhrdd</th>
            <th>Dprm</th>
            <th>Dictm</th>
            <th>Dpl</th>
            <th>Dc</th>
            <th>Drd</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="UserRatings in UserRatings" :key="UserRatings.id">
            <td class="td-btn">
              <button class="pen-btn" @click="openForm(UserRatings)">
                <i class="fa-solid fa-pen fa-lg"></i>
              </button>
            </td>
            <td>{{ UserRatings.office }}</td>
            <td>{{ UserRatings.do }}</td>
            <td>{{ UserRatings.didm }}.00</td>
            <td>{{ UserRatings.di }}.00</td>
            <td>{{ UserRatings.dpcr }}.00</td>
            <td>{{ UserRatings.dl }}.00</td>
            <td>{{ UserRatings.dhrdd }}.00</td>
            <td>{{ UserRatings.dprm }}.00</td>
            <td>{{ UserRatings.dictm }}</td>
            <td>{{ UserRatings.dpl }}</td>
            <td>{{ UserRatings.dc }}</td>
            <td>{{ UserRatings.drd }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <form
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
  </form>
</template>

<script>
import axios from "axios";
export default {
  data() {
    return {
      UserRatings: [],
      users: [],
      selectedUser: null,
      selectedUserId: "",
      selectedTable: 1,
      formVisible: false,
      selectedRatings: {
        id: null,
        office: "",
        do: "",
        didm: "",
        di: "",
        dpcr: "",
        dl: "",
        dhrdd: "",
        dprm: "",
        dictm: "",
        dpl: "",
        dc: "",
        drd: "",
      },
    };
  },

  created() {
    this.getUserRatings();
    this.getUsers();
  },

  methods: {
    async getUserRatings() {
      try {
        const response = await axios.get("/getUserRatings");
        this.UserRatings = response.data;
      } catch (error) {
        console.error("Error fetching Useratings data:", error);
      }
    },

    async getUsers() {
      try {
        const response = await axios.get("/getUsers"); // Replace with your actual endpoint
        this.users = response.data;
      } catch (error) {
        console.error("Error fetching users data:", error);
      }
    },

    async saveUserRates() {
      try {
        const response = await axios.post(
          "/api/saveUserRates",
          this.selectedRatings
        );

        if (response.status === 200) {
          const responseData = response.data;

          if (responseData && responseData.success) {
            console.log(responseData.message);
            this.formVisible = false;
            this.getUserRatings();
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

    openForm(UserRatings) {
      this.selectedRatings = { ...UserRatings };
      this.formVisible = true;
    },

    closeForm() {
      this.formVisible = false;
    },

    async findData() {
      try {
        // Make a request to fetch filtered data based on selected user and table
        const response = await axios.get(
          `/findData/${this.selectedUserId}/${this.selectedTable}`
        );
        this.UserRatings = response.data;
      } catch (error) {
        console.error("Error fetching filtered data:", error);
      }
    },
  },
};
</script>

<style>
label {
  width: 1.3rem;
}
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
  justify-content: flex-end;
  align-items: center;
  padding: 1rem;
}
.options-control {
  display: flex;
  gap: 1rem;
}
</style>
