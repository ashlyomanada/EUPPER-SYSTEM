To achieve the desired structure for your Vue component, you can follow the
provided template and adjust your existing code accordingly. Here's the modified
version of your component to match the structure you provided: html Copy code
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
              <select v-model="selectedMonth" class="month">
                <option v-for="month in months" :key="month" :value="month">
                  {{ month }}
                </option>
              </select>
              Select Year:
              <input
                v-model="selectedYear"
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
          <tr v-for="item in paginatedPpoInfo" :key="item.id">
            <td>{{ item.office }}</td>
            <td>{{ item.do }}</td>
            <td>{{ item.didm }}</td>
            <td>{{ item.di }}</td>
            <td>{{ item.dpcr }}</td>
            <td>{{ item.dl }}</td>
            <td>{{ item.dhrdd }}</td>
            <td>{{ item.dprm }}</td>
            <td>{{ item.dictm }}</td>
            <td>{{ item.dpl }}</td>
            <td>{{ item.dc }}</td>
            <td>{{ item.drd }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="paginate-container">
    <nav aria-label="Page navigation">
      <ul class="pagination">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <button class="page-link" @click="previousPage">&laquo;</button>
        </li>
        <li
          class="page-item"
          v-for="pageNumber in totalPages"
          :key="pageNumber"
          :class="{ active: currentPage === pageNumber }"
        >
          <button class="page-link" @click="goToPage(pageNumber)">
            {{ pageNumber }}
          </button>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <button class="page-link" @click="nextPage">&raquo;</button>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      months: [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December",
      ],
      selectedMonth: "",
      selectedYear: "",
      PpoInfo: [],
      currentPage: 1,
      itemsPerPage: 10, // Adjust this as per your requirement
    };
  },
  computed: {
    totalPages() {
      return Math.ceil(this.PpoInfo.length / this.itemsPerPage);
    },
    paginatedPpoInfo() {
      const startIndex = (this.currentPage - 1) * this.itemsPerPage;
      const endIndex = startIndex + this.itemsPerPage;
      return this.PpoInfo.slice(startIndex, endIndex);
    },
  },
  methods: {
    async getPpoInfo() {
      try {
        const response = await axios.get("getPpo");
        this.PpoInfo = response.data;
      } catch (error) {
        console.error("Error fetching PpoInfo:", error);
      }
    },
    generateExcel() {
      // Implementation for generating Excel report
    },
    generatePdf() {
      // Implementation for generating PDF report
    },
    findData() {
      // Implementation for filtering data
    },
    previousPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
      }
    },
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++;
      }
    },
    goToPage(pageNumber) {
      this.currentPage = pageNumber;
    },
  },
  mounted() {
    this.getPpoInfo();
  },
};
</script>
<style>
#tableppo {
  background: var(--light);
}

.table-container {
  padding: 1rem;
  background: var(--light);
}

.paginate-container {
  display: flex;
  align-items: center;
  justify-content: center;
}
.ppohead {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 0.5rem;
  margin-bottom: 1rem;
}
.ppo-table-data {
  display: flex;
  flex-wrap: wrap;
  grid-gap: 24px;
  margin-top: 24px;
  width: 100%;
  color: var(--dark);
  flex-direction: column;
}
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
