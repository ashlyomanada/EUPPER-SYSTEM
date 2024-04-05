<template>
  <div class="table-data">
    <div class="order">
      <div class="head">
        <div class="head-options">
          <div style="text-align: center">
            <h3>Unit Performance Evaluation Rating</h3>
            <h4>Puerto Prinsesa MPS / CPS Level</h4>
          </div>
          <div class="date-options">
            <div>
              Select Month:<select class="month" name="month">
                <option value="1">January</option>
                <option value="2">February</option>
                <option value="3">March</option>
                <option value="4">April</option>
                <option value="5">May</option>
                <option value="6">June</option>
                <option value="7">July</option>
                <option value="8">August</option>
                <option value="9">September</option>
                <option value="10">October</option>
                <option value="11">November</option>
                <option value="12">December</option>
              </select>
              Select Year:
              <input
                type="number"
                class="year"
                name="year"
                min="1900"
                max="2100"
                step="1"
                placeholder="Year"
              />
              <button class="find"><i class="bx bx-search"></i>Find</button>
            </div>
            <button class="generate">Generate Excel Report</button>
            <button class="generate" @click="generatePdf">
              Generate Pdf Report
            </button>
          </div>
        </div>
      </div>
      <table>
        <thead>
          <tr>
            <th>Month</th>
            <th>Year</th>
            <th>PS1</th>
            <th>PS2</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="rating in usersRate" :key="rating.id">
            <td>{{ rating.month }}</td>
            <td>{{ rating.year }}</td>
            <td>{{ rating.ps1 }}</td>
            <td>{{ rating.ps2 }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      usersRate: "",
      dataFetched: false,
      visible: true,
    };
  },
  components: {},
  mounted() {
    this.fetchUserData();
  },
  methods: {
    async fetchUserData() {
      try {
        const response = await axios.get(`/getPuertRates`);
        this.usersRate = response.data;
        this.dataFetched = true;
        // console.log(this.usersRate);
      } catch (e) {
        console.log(e);
      }
    },
  },
};
</script>

<style>
.month,
.year,
.find,
.generate {
  border: 1px solid var(--dark);
  padding: 0.2rem 0.5rem;
}
.head-options {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  width: 100%;
}
.date-options {
  width: 100%;
  display: flex;
  justify-content: space-between;
}
</style>
