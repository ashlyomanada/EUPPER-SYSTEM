<template>
  <div class="table-data">
    <div class="order">
      <div class="head">
        <div class="head-options">
          <div>
            <h3>Unit Performance Evaluation Rating</h3>
            <h4>RMFB / PMFC Level</h4>
          </div>
          <div class="date-options">
            <div>
              Select Month:
              <select
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
          <tr v-for="rates in paginatedData" :key="rates.id">
            <td class="t-data">{{ rates.office }}</td>
            <td class="t-data">{{ rates.rod }}</td>
            <td class="t-data">{{ rates.ridmd }}</td>
            <td class="t-data">{{ rates.rid }}</td>
            <td class="t-data">{{ rates.rcadd }}</td>
            <td class="t-data">{{ rates.rlrdd }}</td>
            <td class="t-data">{{ rates.rlddd }}</td>
            <td class="t-data">{{ rates.rprmd }}</td>
            <td class="t-data">{{ rates.rictmd }}</td>
            <td class="t-data">{{ rates.rpsmd }}</td>
            <td class="t-data">{{ rates.rcd }}</td>
            <td class="t-data">{{ rates.rrd }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div
      style="
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        justify-content: center;
        overflow: hidden;
      "
    >
      <!-- Pagination -->
      <nav aria-label="Page navigation">
        <ul class="pagination">
          <li class="page-item" :class="{ disabled: currentPage === 1 }">
            <button class="page-link" @click="prevPage">&laquo;</button>
          </li>
          <li
            class="page-item"
            v-for="page in totalPages"
            :key="page"
            :class="{ active: page === currentPage }"
          >
            <button class="page-link" @click="changePage(page)">
              {{ page }}
            </button>
          </li>
          <li
            class="page-item"
            :class="{ disabled: currentPage === totalPages }"
          >
            <button class="page-link" @click="nextPage">&raquo;</button>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</template>

<script>
import axios from "axios";
export default {
  data() {
    return {
      RmfbRates: [],
      selectedMonth: "",
      selectedYear: "",
      currentPage: 1,
      itemsPerPage: 10, // Change this as per your requirement
    };
  },
  computed: {
    totalPages() {
      return Math.ceil(this.RmfbRates.length / this.itemsPerPage);
    },
    paginatedData() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.RmfbRates.slice(start, end);
    },
  },
  methods: {
    async getRmfbRates() {
      try {
        const response = await axios.get(`/viewRmfbRates`);
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
    // Pagination methods
    changePage(page) {
      this.currentPage = page;
    },
    prevPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
      }
    },
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++;
      }
    },
  },
  mounted() {
    this.getRmfbRates();
  },
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
