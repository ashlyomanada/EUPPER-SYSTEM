<template>
  <div class="table-data">
    <div class="order">
      <div class="head">
        <div class="head-options">
          <div style="text-align: center">
            <h3>Unit Performance Evaluation Rating</h3>
            <h4>PPO / CPO Level</h4>
          </div>
          <div class="date-options">
            <div class="d-flex gap-2 align-items-center">
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
              <button
                class="find d-flex align-items-center"
                @click="getUsersRateByMonth"
              >
                <i class="bx bx-search"></i>Find
              </button>
            </div>
            <div class="d-flex gap-2">
              <button class="generate" @click="confirmationGenerateExcel">
                Generate Excel Report
              </button>
              <button class="generate" @click="confirmationGeneratePdf">
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

  <div
    class="modal fade"
    id="ppoExcelConfirmation"
    tabindex="-1"
    aria-labelledby="successModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-body text-center">
          <p>
            Are you sure do you want to generate an Excel Report for PPO?,
            please check if all users rated!
          </p>

          <div class="d-flex justify-content-center gap-3">
            <button
              type="button"
              class="btn btn-primary"
              @click="generatePPOReport"
            >
              Yes
            </button>
            <button
              type="button"
              class="btn btn-danger"
              data-bs-dismiss="modal"
            >
              No
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div
    class="modal fade"
    id="ppoPdfConfirmation"
    tabindex="-1"
    aria-labelledby="successModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-body text-center">
          <p>
            Are you sure do you want to generate an PDF Report for PPO?, please
            check if all users rated!
          </p>

          <div class="d-flex justify-content-center gap-3">
            <button type="button" class="btn btn-primary" @click="generatePdf">
              Yes
            </button>
            <button
              type="button"
              class="btn btn-danger"
              data-bs-dismiss="modal"
            >
              No
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import * as XLSX from "xlsx";
import axios from "axios";
import { Modal } from "bootstrap";

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
      allOffices: [],
      baseURL: axios.defaults.baseURL,
    };
  },
  created() {
    this.fetchColumns();
    this.getUsersRate();
    this.getUsersOffice();
    const currentDate = new Date();
    this.month = currentDate.toLocaleString("default", {
      month: "long",
    });
    this.year = currentDate.getFullYear();
  },

  methods: {
    confirmationGenerateExcel() {
      const modalElement = document.getElementById("ppoExcelConfirmation");
      const modalInstance = new Modal(modalElement);
      modalInstance.show();
    },
    confirmationGeneratePdf() {
      const modalElement = document.getElementById("ppoPdfConfirmation");
      const modalInstance = new Modal(modalElement);
      modalInstance.show();
    },
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

    async getUsersRate() {
      try {
        const currentDate = new Date();
        const currentMonth = currentDate.toLocaleString("default", {
          month: "long",
        }); // Get full month name (e.g., "April")
        const currentYear = currentDate.getFullYear(); // Get the current year (e.g., 2024)

        // Make a POST request to fetch users' rate data for the current month and year
        const response = await axios.post("/getUsersRatePPO", {
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
        const response = await axios.post("/getUsersRatePPOByMonth", {
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

    async generatePPOReport() {
      try {
        const response = await axios.post(
          `${this.baseURL}generatePPOOffice`,
          {
            month: this.month,
            year: this.year,
          },
          { responseType: "blob" }
        );
        const url = window.URL.createObjectURL(new Blob([response.data]));

        // Construct the file name using the month and year values
        const fileName = `PPO_Report_${this.month}_${this.year}.xlsx`;

        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", fileName);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        const modalElement = document.getElementById("ppoExcelConfirmation");
        const modalInstance =
          Modal.getInstance(modalElement) || new Modal(modalElement);

        // Hide the modal
        modalInstance.hide();
      } catch (error) {
        console.error("Error generating report:", error);
      }
    },

    generatePdf() {
      // Replace 'your-server-url' with the actual URL of your server
      axios
        .post(
          "/generatePdfPPO",
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
          a.download = `PPO_Report_${this.month}_${this.year}.pdf`; // Set the filename for download
          document.body.appendChild(a);
          a.click();
          window.URL.revokeObjectURL(url);

          const modalElement = document.getElementById("ppoPdfConfirmation");
          const modalInstance =
            Modal.getInstance(modalElement) || new Modal(modalElement);

          // Hide the modal
          modalInstance.hide();
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

@media screen and (max-width: 1100px) {
  .date-options {
    flex-direction: column;
    gap: 1rem;
    align-items: center;
  }
}
</style>
