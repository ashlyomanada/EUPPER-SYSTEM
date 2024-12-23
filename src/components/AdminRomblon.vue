<template>
  <div class="table-data">
    <div class="order">
      <div class="head">
        <div class="head-options">
          <div style="text-align: center">
            <h3>Unit Performance Evaluation Rating</h3>
            <h4>Municipalities of Romblon</h4>
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
                class="btn btn-primary d-flex align-items-center"
                @click="getUsersRateByMonth"
              >
                <i class="bx bx-search"></i>Find
              </button>
            </div>
            <div class="d-flex gap-2">
              <button
                class="btn btn-success"
                @click="confirmationGenerateExcel"
                :disabled="isLoadingExcel"
              >
                <span v-if="!isLoadingExcel"> Generate Excel Report</span>
                <span v-if="isLoadingExcel">
                  <i class="fa-solid fa-spinner"></i> Downloading Report</span
                >
              </button>
              <button
                class="btn btn-success"
                @click="previewGeneratePdf"
                :disabled="isLoading"
              >
                <span v-if="!isPreview"> Preview PDF Report</span>
                <span v-if="isPreview">
                  <i class="fa-solid fa-spinner"></i> Previewing Report</span
                >
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
    id="romblonExcelConfirmation"
    tabindex="-1"
    aria-labelledby="successModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-body text-center">
          <p>
            Are you sure do you want to generate an Excel Report for Romblon?,
            please check if all users rated!
          </p>

          <div class="d-flex justify-content-center gap-3">
            <button
              type="button"
              class="btn btn-primary"
              @click="generateRomReport"
              :disabled="isLoadingExcel"
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
    id="romblonPdfConfirmation"
    tabindex="-1"
    aria-labelledby="successModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-body text-center">
          <p>
            Are you sure do you want to generate an PDF Report for Romblon?,
            please check if all users rated!
          </p>

          <div class="d-flex justify-content-center gap-3">
            <button
              type="button"
              class="btn btn-primary"
              @click="generatePdf"
              :disabled="isLoading"
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
    id="previewPDF"
    tabindex="-1"
    aria-labelledby="successModalLabel"
  >
    <div
      class="modal-dialog modal-dialog-centered"
      style="display: flex; justify-content: center"
    >
      <div
        class="modal-content"
        style="
          background: var(--light);
          color: var(--dark);
          display: flex;
          justify-content: center;
          align-items: center;
        "
      >
        <div class="modal-header">
          <h5 class="modal-title">Preview PDF Report</h5>
        </div>
        <div class="modal-body" style="" id="modalContent">
          <div class="customization-controls">
            <label for="pageSize">Page Size:</label>
            <select id="pageSize" v-model="size">
              <option value="A4">A4</option>
              <option value="A3">A3</option>
              <option value="Letter">Letter</option>
            </select>

            <label for="orientation">Orientation:</label>
            <select id="orientation" v-model="orientation">
              <option value="P">Portrait</option>
              <option value="L">Landscape</option>
            </select>

            <button class="btn btn-primary" @click="previewGeneratePdf()">
              Apply
            </button>
          </div>
          <div
            id="pdfViewer"
            style="
              width: 100%;
              overflow-y: auto;
              display: flex;
              flex-direction: column;
              gap: 1rem;
              box-shadow: rgba(0, 0, 0, 0.3) 0px 2px 4px,
                rgba(0, 0, 0, 0.2) 0px 7px 13px -3px,
                rgba(0, 0, 0, 0.1) 0px -3px 0px inset;
            "
          ></div>
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal"
          >
            Close
          </button>
          <button
            type="button"
            @click.prevent="generatePdf"
            class="btn btn-primary"
          >
            <span v-if="!isLoading"> Generate PDF Report</span>
            <span v-if="isLoading">
              <i class="fa-solid fa-spinner"></i> Downloading Report</span
            >
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import * as XLSX from "xlsx";
import axios from "axios";
import { Modal } from "bootstrap";
import { GlobalWorkerOptions, getDocument } from "pdfjs-dist";
GlobalWorkerOptions.workerSrc = `/pdf.worker.min.mjs`;

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
      isLoading: false,
      isLoadingExcel: false,
      url: "",
      isPreview: false,
      size: "A4",
      orientation: "P",
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
    async renderPdf(pdfUrl) {
      // Show the modal
      const modalElement = document.getElementById("previewPDF");
      let modalInstance = Modal.getInstance(modalElement);
      if (!modalInstance) {
        modalInstance = new Modal(modalElement);
      }

      modalInstance.show();

      const viewerContainer = document.getElementById("pdfViewer");
      viewerContainer.innerHTML = "";

      try {
        const pdf = await pdfjsLib.getDocument(pdfUrl).promise;

        for (let i = 1; i <= pdf.numPages; i++) {
          const page = await pdf.getPage(i);
          // Create a canvas element for each page
          const canvas = document.createElement("canvas");
          canvas.style.display = "block";
          canvas.style.width = "100%";
          canvas.style.boxShadow =
            "rgba(0, 0, 0, 0.3) 0px 2px 4px, rgba(0, 0, 0, 0.2) 0px 7px 13px -3px, rgba(0, 0, 0, 0.1) 0px -3px 0px inset";

          viewerContainer.appendChild(canvas);

          // Render the PDF page onto the canvas
          const context = canvas.getContext("2d");
          const viewport = page.getViewport({ scale: 1.5 }); // Adjust scale as needed
          canvas.width = viewport.width;
          canvas.height = viewport.height;

          await page.render({
            canvasContext: context,
            viewport: viewport,
          }).promise;
        }
      } catch (error) {
        console.error("Error rendering PDF:", error);
      }
    },
    previewGeneratePdf() {
      this.isPreview = true;
      axios
        .post(
          "/generatePdf",
          {
            month: this.month,
            year: this.year,
            level: "romblon_cps",
            officeName: "PROVINCIAL/CITY POLICE OFFICES",
            size: this.size,
            orientation: this.orientation,
          },
          {
            responseType: "blob", // Handle binary data
          }
        )
        .then((response) => {
          // Create a Blob and URL for the PDF
          const blob = new Blob([response.data], { type: "application/pdf" });

          const pdfUrl = URL.createObjectURL(blob);
          // Render the PDF in the viewer
          this.renderPdf(pdfUrl);
          this.isPreview = false;
        })
        .catch((error) => {
          console.error("Error generating PDF:", error);
          this.isPreview = false;
        });
    },

    confirmationGenerateExcel() {
      const modalElement = document.getElementById("romblonExcelConfirmation");
      const modalInstance = new Modal(modalElement);
      modalInstance.show();
    },
    confirmationGeneratePdf() {
      const modalElement = document.getElementById("romblonPdfConfirmation");
      const modalInstance = new Modal(modalElement);
      modalInstance.show();
    },

    async fetchColumns() {
      try {
        const response = await axios.get("/getColumnNameRom");
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
        const response = await axios.post("/getUsersRateRom", {
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
        const response = await axios.post("/getUsersRateRomByMonth", {
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

    async generateRomReport() {
      try {
        this.isLoadingExcel = true;
        const response = await axios.post(
          `${this.baseURL}generateRomReport`,
          {
            month: this.month,
            year: this.year,
          },
          { responseType: "blob" }
        );
        const url = window.URL.createObjectURL(new Blob([response.data]));

        // Construct the file name using the month and year values
        const fileName = `Romblon_Report_${this.month}_${this.year}.xlsx`;

        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", fileName);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        const modalElement = document.getElementById(
          "romblonExcelConfirmation"
        );
        const modalInstance =
          Modal.getInstance(modalElement) || new Modal(modalElement);

        // Hide the modal
        setTimeout(() => (this.isLoadingExcel = false), 1000);

        modalInstance.hide();
      } catch (error) {
        console.error("Error generating report:", error);
      }
    },

    generatePdf() {
      // Replace 'your-server-url' with the actual URL of your server
      this.isLoading = true;
      axios
        .post(
          "/generatePdf",
          {
            month: this.month,
            year: this.year,
            level: "romblon_cps",
            officeName: "PROVINCIAL/CITY POLICE OFFICES",
            size: this.size,
            orientation: this.orientation,
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
          a.download = `Romblon_Report_${this.month}_${this.year}.pdf`;
          document.body.appendChild(a);
          a.click();
          window.URL.revokeObjectURL(url);

          const modalElement = document.getElementById(
            "romblonPdfConfirmation"
          );
          const modalInstance =
            Modal.getInstance(modalElement) || new Modal(modalElement);

          // Hide the modal
          setTimeout(() => (this.isLoading = false), 1000);
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
</style>
