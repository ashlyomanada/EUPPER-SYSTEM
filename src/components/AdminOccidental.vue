<template>
  <div class="table-data">
    <div class="order">
      <div class="head">
        <div class="head-options">
          <div style="text-align: center">
            <h3>Unit Performance Evaluation Rating</h3>
            <h4>Municipalities of Occidental Mindoro</h4>
          </div>
          <div class="date-options">
            <div class="d-flex gap-2">
              Select Month:
              <select class="month" name="month" v-model="month">
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
                min="2000"
                max="2100"
                step="1"
                placeholder="Year"
                v-model="year"
              />
              <button class="find" @click="getUsersRateByMonth">
                <i class="bx bx-search"></i>Find
              </button>
            </div>
            <button class="generate" @click="generateOcciReport">
              Generate Excel Report
            </button>
          </div>
        </div>
      </div>
      <table>
        <thead>
          <tr>
            <th class="t-row">Office/Unit</th>
            <th
              class="t-row"
              v-for="(office, index) in allOffices"
              :key="index"
            >
              {{ office }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(column, colIndex) in columns" :key="colIndex">
            <td>{{ column.replace(/_/g, " ") }}</td>
            <template
              v-for="(office, officeIndex) in allOffices"
              :key="officeIndex"
            >
              <td>
                <!-- Find the corresponding rate for this office and column -->
                {{ findRateForOfficeAndColumn(office, column) }}
              </td>
            </template>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import * as XLSX from "xlsx";
import axios from "axios";

export default {
  data() {
    return {
      UsersOffice: [],
      columns: [],
      UsersRate: [],
      month: "",
      year: "",
      userId: "",
      allOffices: [], // Array to store all office names
    };
  },
  created() {
    this.fetchColumns();
    this.getUsersRate();
    this.getUsersOffice();
  },

  methods: {
    async fetchColumns() {
      try {
        const response = await axios.get("/getColumnNameOcci");
        this.columns = response.data.filter(
          (column) => !["id", "userid", "month", "year"].includes(column)
        );
      } catch (error) {
        console.error("Error fetching column names:", error);
      }
    },

    async getUsersRate() {
      try {
        const response = await axios.get("/getUsersRateOcci");
        // Exclude specified properties from each object in the response data
        this.UsersRate = response.data.map(
          ({ id, userid, month, year, ...rest }) => ({
            ...rest, // Spread the rest of the properties
          })
        );
        //console.log(this.UsersRate);
      } catch (e) {
        console.log(e);
      }
    },

    async getUsersRateByMonth() {
      try {
        const response = await axios.post("/getUsersRateByMonth", {
          Month: this.month,
          Year: this.year,
        });
        this.UsersRate = response.data.map(
          ({ id, userid, month, year, ...rest }) => ({
            ...rest, // Spread the rest of the properties
          })
        );
      } catch (error) {
        console.error("Error filtering users rate:", error);
      }
    },

    async getUsersOffice() {
      try {
        const response = await axios.get("/getUsersOffice");
        // Extract office names from the response
        this.allOffices = response.data.map((office) => office.office);
      } catch (e) {
        console.log(e);
      }
    },

    findRateForOfficeAndColumn(office, column) {
      // Find the rate corresponding to the given office and column
      const rate = this.UsersRate.find((rate) => rate.office === office);
      if (rate) {
        return rate[column];
      } else {
        return ""; // Return empty string if no corresponding rate is found
      }
    },

    async generateOcciReport() {
      try {
        const response = await axios.post(
          "http://localhost:8080/generateOcciReport",
          {
            month: this.month,
            year: this.year,
          },
          { responseType: "blob" }
        );
        const url = window.URL.createObjectURL(new Blob([response.data]));

        // Construct the file name using the month and year values
        const fileName = `Municipality_of_Occidental_Mindoro_Report_${this.month}_${this.year}.xlsx`;

        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", fileName);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      } catch (error) {
        console.error("Error generating report:", error);
      }
    },
  },
};
</script>

<style>
select {
  color: var(--dark);
}
option {
  color: var(--dark);
  background: var(--light);
}
.year {
  color: var(--dark);
}
.month,
.year,
.find,
.generate {
  padding: 0.2rem 0.5rem;
  border-radius: 0.4rem;
}
.generate {
  background: green;
  color: white;
}
.find {
  background: var(--blue);
  color: white;
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
