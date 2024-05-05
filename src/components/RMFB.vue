<template>
  <div class="table-data">
    <div class="order">
      <div class="head">
        <div class="head-options">
          <div style="text-align: center">
            <h3>Unit Performance Evaluation Rating</h3>
            <h4>RMFB / PMFC Level</h4>
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
            <div class="d-flex gap-2">
              <button class="generate" @click="generateRMFBReport">
                Generate Excel Report
              </button>
              <button class="generate" @click="generatePdf">
                Generate PDF Report
              </button>
            </div>
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
        <tbody v-if="!noData">
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
      <h5 style="text-align: center" v-if="noData">No data found</h5>
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
      noData: false,
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
        const response = await axios.get("/getColumnNameRMFB");
        this.columns = response.data.filter(
          (column) => !["id", "userid", "month", "year"].includes(column)
        );
      } catch (error) {
        console.error("Error fetching column names:", error);
      }
    },

    async getUsersRate() {
      try {
        const currentDate = new Date();
        const currentMonth = currentDate.toLocaleString("default", {
          month: "long",
        }); // Get full month name (e.g., "April")
        const currentYear = currentDate.getFullYear(); // Get the current year (e.g., 2024)

        // Make a POST request to fetch users' rate data for the current month and year
        const response = await axios.post("/getUsersRateRMFB", {
          Month: currentMonth,
          Year: currentYear,
        });
        if (response.status === 200) {
          this.UsersRate = response.data.map(
            ({ id, userid, month, year, ...rest }) => ({
              ...rest, // Spread the rest of the properties
            })
          );

          // Check if there are any users' rates for the selected month and year
          this.noData = this.UsersRate.length === 0;
        } else {
          // If the response status is not 200, handle it as an error
          console.error("Error: Unexpected status code", response.status);
          this.noData = true;
          this.UsersRate = []; // Reset UsersRate to an empty array
        }
      } catch (error) {
        console.error("Error filtering users rate:", error);
        this.noData = true; // Set noData to true in case of any error
        this.UsersRate = []; // Reset UsersRate to an empty array
      }
    },

    async getUsersRateByMonth() {
      try {
        const response = await axios.post("/getUsersRateRMFBByMonth", {
          Month: this.month,
          Year: this.year,
        });

        if (response.status === 200) {
          this.UsersRate = response.data.map(
            ({ id, userid, month, year, ...rest }) => ({
              ...rest, // Spread the rest of the properties
            })
          );

          // Check if there are any users' rates for the selected month and year
          this.noData = this.UsersRate.length === 0;
        } else {
          // If the response status is not 200, handle it as an error
          console.error("Error: Unexpected status code", response.status);
          this.noData = true;
          this.UsersRate = []; // Reset UsersRate to an empty array
        }
      } catch (error) {
        console.error("Error filtering users rate:", error);
        this.noData = true; // Set noData to true in case of any error
        this.UsersRate = []; // Reset UsersRate to an empty array
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

    async generateRMFBReport() {
      try {
        const response = await axios.post(
          "http://localhost:8080/generateRMFBOffice",
          {
            month: this.month,
            year: this.year,
          },
          { responseType: "blob" }
        );
        const url = window.URL.createObjectURL(new Blob([response.data]));

        // Construct the file name using the month and year values
        const fileName = `RMFB_Report_${this.month}_${this.year}.xlsx`;

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

    generatePdf() {
      // Replace 'your-server-url' with the actual URL of your server
      axios
        .post(
          "/generatePdfRMFB",
          {
            month: this.month,
            year: this.year,
          },
          {
            responseType: "blob", // Set the response type to 'blob' to handle binary data
          }
        )
        .then((response) => {
          // Handle the PDF response here, e.g., initiate download
          const blob = new Blob([response.data], { type: "application/pdf" });
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement("a");
          a.href = url;
          a.download = `RMFB_Report_${this.month}_${this.year}.pdf`;
          document.body.appendChild(a);
          a.click();
          window.URL.revokeObjectURL(url);
        })
        .catch((error) => {
          console.error("Error generating PDF:", error);
        });
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
