<template>
  <div class="table-data">
    <div class="order">
      <div class="head">
        <div>
          <h3>Unit Performance Evaluation Rating</h3>
          <h4>Municipalities of Occidental</h4>
        </div>
      </div>
      <table v-if="dataFetched">
        <thead>
          <tr>
            <th>Action</th>
            <th>Office</th>
            <th>ROD</th>
            <th>RIDMD</th>
            <th>RID</th>
            <th>RCADD</th>
            <th>RLRDD</th>
            <th>RLDDD</th>
            <th>RPRMD</th>
            <th>RICTMD</th>
            <th>RPSMD</th>
            <th>RCD</th>
            <th>RRD</th>
          </tr>
          <tr v-for="rating in userRatings" :key="rating.id">
            <td class="td-btn">
              <button
                class="pen-btn"
                @click="openForm(rating)"
                v-if="UsersInfo[0]?.status === 'Enable'"
              >
                <i class="fa-solid fa-pen fa-lg"></i>
              </button>
            </td>
            <td class="t-data">{{ rating.office }}</td>
            <td class="t-data">{{ rating.rod }}</td>
            <td class="t-data">{{ rating.ridmd }}</td>
            <td class="t-data">{{ rating.rid }}</td>
            <td class="t-data">{{ rating.rcadd }}</td>
            <td class="t-data">{{ rating.rlrdd }}</td>
            <td class="t-data">{{ rating.rlddd }}</td>
            <td class="t-data">{{ rating.rprmd }}</td>
            <td class="t-data">{{ rating.rictmd }}</td>
            <td class="t-data">{{ rating.rpsmd }}</td>
            <td class="t-data">{{ rating.rcd }}</td>
            <td class="t-data">{{ rating.rrd }}</td>
          </tr>
        </thead>
      </table>
      <h2 v-else>No Ratings Yet</h2>
    </div>
  </div>
  <div class="modal-background" :class="{ 'dim-overlay': formVisible }">
    <form
      class="form"
      id="rating-form-edit2"
      :style="{ display: formVisible ? 'block' : 'none' }"
    >
      <div class="form-edit">
        <label class="rate-labels" for="">Office:</label>
        <input
          v-model="selectedRatings.office"
          type="text"
          placeholder=""
          class="input"
        />
        <label class="rate-labels" for="">ROD:</label>
        <input
          v-model="selectedRatings.rod"
          type="text"
          placeholder=""
          class="input"
        />
        <label class="rate-labels" for="">RIDMD:</label>
        <input
          v-model="selectedRatings.ridmd"
          type="text"
          placeholder=" Mindoro"
          class="input"
        />
      </div>
      <div class="form-edit">
        <label class="rate-labels" for="">RID:</label>
        <input
          v-model="selectedRatings.rid"
          type="text"
          placeholder=" Mindoro"
          class="input"
        />
        <label class="rate-labels" for="">RCADD:</label>
        <input
          v-model="selectedRatings.rcadd"
          type="text"
          placeholder=""
          class="input"
        />
        <label class="rate-labels" for="">RLRDD:</label>
        <input
          v-model="selectedRatings.rlrdd"
          type="text"
          placeholder=""
          class="input"
        />
      </div>
      <div class="form-edit">
        <label class="rate-labels" for="">RLDDD:</label>
        <input
          v-model="selectedRatings.rlddd"
          type="text"
          placeholder=""
          class="input"
        />
        <label class="rate-labels" for="">RPRMD:</label>
        <input
          v-model="selectedRatings.rprmd"
          type="text"
          placeholder=""
          class="input"
        />
        <label class="rate-labels" for="">RICTMD:</label>
        <input
          v-model="selectedRatings.rictmd"
          type="text"
          placeholder=""
          class="input"
        />
      </div>
      <div class="form-edit">
        <label class="rate-labels" for="">RPSMD:</label>
        <input
          v-model="selectedRatings.rpsmd"
          type="text"
          placeholder=""
          class="input"
        />
        <label class="rate-labels" for="">RCD:</label>
        <input
          v-model="selectedRatings.rcd"
          type="text"
          placeholder=""
          class="input"
        />
        <label class="rate-labels" for="">RRD:</label>
        <input
          v-model="selectedRatings.rrd"
          type="text"
          placeholder=""
          class="input"
        />
      </div>
      <div class="modal-buttons">
        <button @click.prevent="saveUserRates">Save</button>
        <button @click.prevent="closeForm">Close</button>
      </div>
    </form>
  </div>
</template>

<script>
import axios from "axios";
export default {
  data() {
    return {
      UsersInfo: "",
      dataFetched: false,
      userRatings: [],
      formVisible: false,
      selectedRatings: {
        id: null,
        office: "",
        rod: "",
        ridmd: "",
        rid: "",
        rcadd: "",
        rlrdd: "",
        rlddd: "",
        rprmd: "",
        rictmd: "",
        rpsmd: "",
        rcd: "",
        rrd: "",
      }, // Use an array to store all ratings
    };
  },

  mounted() {
    this.fetchData();
    this.getUsersInfo();
  },

  methods: {
    // Example log in the methods
    async getUsersInfo() {
      try {
        const UsersInfo = await axios.get("getUsersInfo");
        this.UsersInfo = UsersInfo.data;
        // console.log("UsersInfo:", this.UsersInfo); // Log the entire UsersInfo object
      } catch (e) {
        console.error("Error fetching UsersInfo:", e);
      }
    },

    fetchData() {
      const storedUserId = sessionStorage.getItem("id");

      if (storedUserId) {
        axios
          .get(`/userMpsOcciRates/${storedUserId}`)
          .then((response) => {
            this.userRatings = response.data;
            this.dataFetched = true;
          })
          .catch((error) => {
            console.error("Error fetching user data:", error);
          });
      }
    },

    openForm(UserRatings) {
      this.selectedRatings = { ...UserRatings };
      this.formVisible = true;
    },

    async saveUserRates() {
      try {
        const response = await axios.post(
          "saveRmfbUserRates",
          this.selectedRatings
        );

        if (response.status === 200) {
          const responseData = response.data;

          if (responseData && responseData.success) {
            //console.log(responseData.message);
            this.formVisible = false;
            this.fetchData();
            this.getUsersInfo();
            //console.log(response);
          } else {
            console.error("Save failed:", responseData.message);
            // Handle error here, show a message to the user or take appropriate action
          }
        } else {
          console.error(`Unexpected response status: ${response.status}`);
          // Handle unexpected response status
        }
      } catch (error) {
        console.error("Error saving user rates:", error);
        // Handle other errors, e.g., network error
      }
    },
    closeForm() {
      this.formVisible = false;
    },
  },
};
</script>

<style>
.dim-overlay {
  position: fixed;
  display: flex;
  justify-content: center;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(
    0,
    0,
    0,
    0.5
  ); /* Adjust the last value for the desired transparency */
  z-index: 1;
  /* Make sure the overlay is above other elements */
}
.rate-labels {
  width: 1.3rem;
}
#rating-form-edit2 {
  position: absolute;
  width: 70%;
  top: 25%;
  left: 26%;
  display: none;
  z-index: 2;
}
.form-edit {
  display: flex;
  gap: 2rem;
  align-items: center;
}
.pen-btn {
  color: rgb(233, 70, 70);
}
</style>
