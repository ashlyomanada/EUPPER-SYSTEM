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
            <div>
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
            <button class="generate" @click="generateExcel">
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
            <th
              class="t-row"
              v-for="(office, index) in UsersOffice"
              :key="index"
            >
              {{ office.office }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(column, colIndex) in columns" :key="colIndex">
            <td>{{ column.replace(/_/g, " ") }}</td>
            <template v-for="(rate, rateIndex) in UsersRate" :key="rateIndex">
              <td>
                {{ rate[column] }}
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
    };
  },
  created() {
    this.fetchColumns();
    this.getUsersOffice();
    this.getUsersRate();
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

    async getUsersOffice() {
      try {
        const response = await axios.get("/getUsersOffice");
        this.UsersOffice = response.data;
        // console.log(this.UsersOffice);
      } catch (e) {
        console.log(e);
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
        this.UsersRate = response.data;
        this.UsersRate.forEach((item) => {
          item.userid = this.userId;
        });
        this.getUsersRateByOffice();
      } catch (error) {
        console.error("Error filtering users rate:", error);
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
