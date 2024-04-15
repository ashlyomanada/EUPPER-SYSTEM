<template>
  <div
    class="table-data"
    :style="{ display: buttonVisible ? 'block' : 'none' }"
  >
    <div class="order">
      <div class="rating-header">
        <div>
          <h3>Unit Performance Evaluation Rating</h3>
          <h4 class="head-subtitle">Municipalities of Puerto Princesa</h4>
        </div>
      </div>
      <form @submit.prevent="saveRating" class="ratingsheet-container">
        <div class="rateDate">
          <select class="rateMonth" v-model="Month" name="month">
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
          <input
            type="number"
            class="rateYear"
            name="year"
            min="2000"
            max="2100"
            step="1"
            placeholder="Year"
            v-model="Year"
            required
          />
        </div>
        <div
          v-for="(column, index) in columns"
          :key="index"
          style="width: 100%; display: flex; justify-content: center"
        >
          <input
            type="number"
            class="rateInput"
            :placeholder="getPlaceholder(column)"
            v-model="formData[column]"
            required
            min="0"
            :max="getMaxRateByOffice(office)"
          />
        </div>
        <button class="submitPPORate" type="submit">Submit</button>
      </form>
    </div>
  </div>
  <div class="modalBg" v-if="formVisible">
    <div class="alertBox">
      <img class="checkImg" src="./img/check2.gif" alt="" />
      <h1 class="alertContent">Successfully Rated</h1>
      <button class="btn btn-primary" @click="okayBtn">Okay</button>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      selectedRating: null,
      buttonVisible: true,
      formVisible: false,
      UserId: "",
      Month: "",
      Year: "",
      columns: [],
      formData: {},
      office: "",
    };
  },

  created() {
    this.fetchColumns();
    this.fetchUserData();
  },

  methods: {
    getMaxRateByOffice(office) {
      switch (office) {
        case "ROD":
          return 167;
        case "RIDMD":
          return 166;
        case "RID":
          return 167;
        case "RCADD":
          return 100;
        case "RLRDD":
        case "RLDDD":
        case "RPRMD":
        case "RICTMD":
          return 80;
        case "RPSMD":
          return 35;
        case "RCD":
          return 25;
        case "RAD":
          return 20;
        default:
          return 0;
      }
    },

    async fetchUserData() {
      const storedUserId = sessionStorage.getItem("id");
      if (storedUserId) {
        try {
          const response = await axios.get(`/getUserData/${storedUserId}`);
          if (response.status === 200) {
            const userData = response.data;
            this.office = userData.office;
            //console.log(this.office);
          } else {
            console.error(`Unexpected response status: ${response.status}`);
          }
        } catch (e) {
          console.log(e);
        }
      }
    },
    async fetchColumns() {
      try {
        const response = await axios.get("/getColumnNamePuer");
        this.columns = response.data.filter(
          (column) => !["id", "userid", "month", "year"].includes(column)
        );
        this.columns.forEach((column) => {
          // Initialize formData with empty values for each column
          this.formData[column] = "";
        });
      } catch (error) {
        console.error("Error fetching column names:", error);
      }
    },

    async saveRating() {
      try {
        this.UserId = sessionStorage.getItem("id");
        const data = {
          UserId: this.UserId,
          Month: this.Month,
          Year: this.Year,
          ...this.formData, // Include formData properties
        };

        // Log the data to console
        //console.log("Data to be saved:", data);

        // Send data to server for insertion
        const response = await axios.post("/insertDataPuer", data);

        if (response.status === 200) {
          // Data successfully saved
          this.Month = "";
          this.Year = "";
          Object.keys(this.formData).forEach((key) => {
            this.formData[key] = "";
          });
          this.formVisible = true;
          setTimeout(() => {
            this.formVisible = false;
          }, 5000);
        } else {
          // Display error message to user
          console.error("Failed to save data.");
        }
      } catch (error) {
        // Display error message to user
        console.error("Error:", error);

        // Optionally, you can also display an error message to the user
        // using a toast or some other notification mechanism
      }
    },
    okayBtn() {
      this.formVisible = false;
    },

    getPlaceholder(column) {
      return column.replace(/_/g, " ");
    },
  },
};
</script>
