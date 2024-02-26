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
              />
              <button class="find" @click="findData">
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
          "https://e-upper.online/backend/generate",
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
      const selectedMonth = document.querySelector(".month").value;
      const selectedYear = document.querySelector(".year").value;

      fetch(
        `https://e-upper.online/backend/generatePdf/${selectedMonth}/${selectedYear}`,
        {
          method: "GET",
          headers: {
            "Content-Type": "application/json",
          },
        }
      )
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

    findData() {
      // Get selected month and year
      const selectedMonth = document.querySelector(".month").value;
      const selectedYear = document.querySelector(".year").value;

      // Filter data based on selected month and year
      this.PpoInfo = this.PpoInfo.filter((item) => {
        return item.month === selectedMonth && item.year === selectedYear;
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
