<template>
  <div class="table-data">
    <div class="order">
      <div class="head">
        <div class="head-options">
          <div>
            <h3>Unit Performance Evaluation Rating</h3>
            <h4>MPS / CPS Level</h4>
          </div>
          <div class="date-options">
            <div>
              Select Month:<select
                class="month"
                name="month"
                v-model="selectedMonth"
                required
              >
                <option value="January">January</option>
                <option value="February">February</option>
                <option value="March">March</option>
                <option value="April">April</option>
                <option value="May">May</option>
                <option value="June">June</option>
                <option value="July">July</option>
                <option value="August">August</option>
                <option value="September">September</option>
                <option value="October">October</option>
                <option value="November">November</option>
                <option value="December">December</option>
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
                v-model="selectedYear"
                required
              />
              <button class="find" @click="getFilterRmfbRates">
                <i class="bx bx-search"></i>Find
              </button>
            </div>
            <button class="generate" @click="generateRmfbReport">
              Generate Excel Report
            </button>
            <button class="generate" @click="generatePdf">
              Generate Pdf Report
            </button>
          </div>
        </div>
      </div>
      <table>
        <thead>
          <tr>
            <th class="t-row">Office/Unit</th>
            <th class="t-row">ROD</th>
            <th class="t-row">RIDMD</th>
            <th class="t-row">RID</th>
            <th class="t-row">RCADD</th>
            <th class="t-row">RLRDD</th>
            <th class="t-row">RLDDD</th>
            <th class="t-row">RPRMD</th>
            <th class="t-row">RICTMD</th>
            <th class="t-row">RPSMD</th>
            <th class="t-row">RCD</th>
            <th class="t-row">RRD</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="rates in RmfbRates" :key="rates.id">
            <td class="t-data">{{ rates.month }}</td>
            <td class="t-data">{{ rates.year }}</td>
            <td class="t-data">{{ rates.regional }}</td>
            <td class="t-data">{{ rates.occi }}</td>
            <td class="t-data">{{ rates.ormin }}</td>
            <td class="t-data">{{ rates.marin }}</td>
            <td class="t-data">{{ rates.rom }}</td>
            <td class="t-data">{{ rates.pal }}</td>
            <td class="t-data">{{ rates.puertop }}</td>
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
      RmfbRates: "",
      selectedMonth: "",
      selectedYear: "",
    };
  },
  methods: {
    async getRmfbRates() {
      try {
        const response = await axios.get("/getRmfbRates");
        this.RmfbRates = response.data;
      } catch (e) {
        console.log(e);
      }
    },

    async getFilterRmfbRates() {
      try {
        const response = await axios.post("/getFilterRmfbRates", {
          Month: this.selectedMonth,
          Year: this.selectedYear,
        });
        this.RmfbRates = response.data;
        //console.log(this.RmfbRates);
      } catch (e) {
        console.log(e);
      }
    },

    async generateRmfbReport() {
      try {
        const response = await axios.post(
          "http://localhost:8080/generateRmfbReport",
          {
            month: this.selectedMonth,
            year: this.selectedYear,
          },
          { responseType: "blob" }
        );
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", "report.xlsx");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      } catch (error) {
        console.error("Error generating report:", error);
      }
    },
  },

  mounted() {},
};
</script>

<style>
.t-row {
  padding: 0.8rem;
}
.t-data {
  text-align: center;
}
.month,
.year,
.find,
.generate {
  border: 1px solid var(--light);
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
