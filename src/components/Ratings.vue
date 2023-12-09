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
            Select User:<select class="month" name="month">
              <option value="1">R1</option>
            </select>
            Select Table:
            <select class="month" name="month">
              <option value="1">PPO CPO LEVEL</option>
              <option value="2">RMFB PMFC LEVEL</option>
              <option value="3">MPS CPS LEVEL</option>
            </select>
            <button class="find"><i class="bx bx-search"></i>Find</button>
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
    id="modal-form"
    :style="{ display: formVisible ? 'block' : 'none' }"
  >
    <input
      v-model="selectedRatings.month"
      type="text"
      placeholder="Month"
      class="input"
    />
    <input
      v-model="selectedRatings.year"
      type="text"
      placeholder="Year"
      class="input"
    />
    <input
      v-model="selectedRatings.occi"
      type="text"
      placeholder="Occidental Mindoro"
      class="input"
    />
    <input
      v-model="selectedRatings.ormin"
      type="text"
      placeholder="Oriental Mindoro"
      class="input"
    />
    <input
      v-model="selectedRatings.marin"
      type="text"
      placeholder="Marinduque"
      class="input"
    />
    <input
      v-model="selectedRatings.rom"
      type="text"
      placeholder="Romblon"
      class="input"
    />
    <input
      v-model="selectedRatings.pal"
      type="text"
      placeholder="Palawan"
      class="input"
    />
    <input
      v-model="selectedRatings.puertop"
      type="text"
      placeholder="Puerto Princesa"
      class="input"
    />
    <div class="modal-buttons">
      <button @click.prevent="saveAdmin">Save</button>
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
      formVisible: false,
      selectedRatings: {
        id: null,
        month: "",
        year: "",
        occi: "",
        ormin: "",
        marin: "",
        rom: "",
        pal: "",
        puertop: "",
      },
    };
  },

  created() {
    this.getUserRatings();
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

    openForm(UserRatings) {
      this.selectedRatings = { ...UserRatings };
      this.formVisible = true;
    },

    async saveAdmin() {
      try {
        const response = await axios.post("/api/saveAdmin", this.selectedAdmin);

        if (response.status === 200) {
          const responseData = response.data;

          if (responseData && responseData.success) {
            console.log(responseData.message);
            this.formVisible = false;
            this.getAdminsInfo();
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
  },
};
</script>

<style>
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
