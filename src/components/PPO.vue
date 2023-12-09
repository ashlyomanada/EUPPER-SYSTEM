<template>
  <div class="table-data">
    <div class="order">
      <div class="head">
        <div class="head-options">
          <div>
            <h3>Unit Performance Evaluation Rating</h3>
            <h4>PPO / CPO Level</h4>
          </div>
          <div class="date-options">
            <div>
              Select Month:
              <select class="month" name="month">
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
                min="2000"
                max="2100"
                step="1"
                placeholder="Year"
              />
              <button class="find"><i class="bx bx-search"></i>Find</button>
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
            <th>Office/Unit</th>
            <th>DO</th>
            <th>DIDM</th>
            <th>DI</th>
            <th>DPCR</th>
            <th>DL</th>
            <th>DHRDD</th>
            <th>DPRM</th>
            <th>DICTM</th>
            <th>DPL</th>
            <th>DC</th>
            <th>DRD</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="PpoInfo in PpoInfo" :key="PpoInfo.id">
            <td>{{ PpoInfo.office }}</td>
            <td>{{ PpoInfo.do }}</td>
            <td>{{ PpoInfo.didm }}</td>
            <td>{{ PpoInfo.di }}</td>
            <td>{{ PpoInfo.dpcr }}</td>
            <td>{{ PpoInfo.dl }}</td>
            <td>{{ PpoInfo.dhrdd }}</td>
            <td>{{ PpoInfo.dprm }}</td>
            <td>{{ PpoInfo.dictm }}</td>
            <td>{{ PpoInfo.dpl }}</td>
            <td>{{ PpoInfo.dc }}</td>
            <td>{{ PpoInfo.drd }}</td>
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
      PpoInfo: [],
    };
  },
  created() {
    this.getPpoInfo();
  },
  methods: {
    async getPpoInfo() {
      try {
        const PpoInfo = await axios.get("getPpo");
        this.PpoInfo = PpoInfo.data;
      } catch (e) {
        console.error("Error fetching PpoInfo:", e);
      }
    },
    generateExcel() {
      axios
        .post(
          "http://localhost:8080/generate",
          {},
          { responseType: "arraybuffer" }
        )
        .then((response) => {
          // Create a Blob from the binary data
          const blob = new Blob([response.data], {
            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
          });

          // Create a download link
          const link = document.createElement("a");
          link.href = window.URL.createObjectURL(blob);
          link.download = "generated_report.xlsx";

          // Simulate a click on the link to trigger the download
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
        })
        .catch((error) => {
          console.error(
            "Error generating Excel file:",
            error.response ? error.response.data : error.message
          );
          // Handle errors, e.g., show an error message to the user
        });
    },
    generatePdf() {
      fetch("http://localhost:8080/generatePdf", {
        method: "GET", // Use GET instead of POST if you're not sending any data
        headers: {
          "Content-Type": "application/json",
        },
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
          }
          return response.blob();
        })
        .then((blob) => {
          const url = window.URL.createObjectURL(new Blob([blob]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", "output.pdf");
          document.body.appendChild(link);
          link.click();
        })
        .catch((error) => {
          console.error("Error generating or loading PDF:", error);
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
