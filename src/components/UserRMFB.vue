<template>
  <div class="table-data">
    <div class="order">
      <div class="rating-header">
        <div>
          <h2>Unit Performance Evaluation Rating</h2>
          <h4 class="head-subtitle">RMFB / PMFC Level</h4>
        </div>
      </div>
      <table v-if="dataFetched">
        <thead>
          <tr>
            <th class="t-row">Month</th>
            <th class="t-row">Year</th>
            <th v-for="(column, index) in columns" :key="index" class="t-row">
              {{ column.replace(/_/g, " ") }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(rating, index) in paginatedRatings" :key="index">
            <td class="t-data">{{ rating.month }}</td>
            <td class="t-data">{{ rating.year }}</td>
            <td
              v-for="(column, colIndex) in columns"
              :key="colIndex"
              class="t-data"
            >
              {{ rating[column] }}
            </td>
          </tr>
        </tbody>
      </table>
      <h4 v-else style="text-align: center">No Ratings Yet</h4>

      <!-- Bootstrap Pagination -->
      <nav
        v-if="dataFetched"
        class="d-flex justify-content-center align-items-center"
      >
        <ul class="pagination justify-content-center">
          <li class="page-item" :class="{ disabled: currentPage === 1 }">
            <a class="page-link" href="#" @click.prevent="changePage(1)">
              First
            </a>
          </li>
          <li class="page-item" :class="{ disabled: currentPage === 1 }">
            <a
              class="page-link"
              href="#"
              @click.prevent="changePage(currentPage - 1)"
            >
              Previous
            </a>
          </li>
          <li
            class="page-item"
            v-for="page in totalPages"
            :key="page"
            :class="{ active: currentPage === page }"
          >
            <a class="page-link" href="#" @click.prevent="changePage(page)">
              {{ page }}
            </a>
          </li>
          <li
            class="page-item"
            :class="{ disabled: currentPage === totalPages }"
          >
            <a
              class="page-link"
              href="#"
              @click.prevent="changePage(currentPage + 1)"
            >
              Next
            </a>
          </li>
          <li
            class="page-item"
            :class="{ disabled: currentPage === totalPages }"
          >
            <a
              class="page-link"
              href="#"
              @click.prevent="changePage(totalPages)"
            >
              Last
            </a>
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
      dataFetched: false,
      userRatings: [],
      columns: [],
      currentPage: 1,
      itemsPerPage: 12,
    };
  },

  mounted() {
    this.fetchData();
    this.fetchColumns();
  },

  computed: {
    totalPages() {
      return Math.ceil(this.userRatings.length / this.itemsPerPage);
    },
    paginatedRatings() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.userRatings.slice(start, end);
    },
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

    fetchData() {
      const storedUserId = sessionStorage.getItem("id");
      if (storedUserId) {
        axios
          .get(`/viewUserRMFBRates/${storedUserId}`)
          .then((response) => {
            this.userRatings = response.data;
            this.dataFetched = true;
          })
          .catch((error) => {
            console.error("Error fetching user data:", error);
          });
      }
    },

    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
      }
    },
  },
};
</script>

<style>
#rating-form-edit2 {
  position: absolute;
  width: 80%;
  top: 15%;
  left: 10%;
  display: none;
  background-color: var(--grey);
}
.form-edit {
  display: flex;
  gap: 2rem;
  align-items: center;
}
.pen-btn {
  color: rgb(233, 70, 70);
}
.pagination {
  margin-top: 20px;
}
</style>
