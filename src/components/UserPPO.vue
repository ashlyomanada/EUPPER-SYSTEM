<template>
  <div class="table-data">
    <div class="order">
      <div class="head">
        <div>
          <h3>Unit Performance Evaluation Rating</h3>
          <h4>Oriental Mindoro PPO / CPO Level</h4>
        </div>
      </div>
      <table v-if="dataFetched">
        <thead>
          <tr>
            <th>Action</th>
            <th>Month</th>
            <th>Year</th>
            <th>Office</th>
            <th>DO</th>
            <th>DIDM</th>
            <th>DI</th>
            <th>DPCR</th>
            <th>Dl</th>
            <th>Dhrdd</th>
            <th>Dprm</th>
            <th>Dictm</th>
            <th>Dpl</th>
            <th>Dc</th>
            <th>Drd</th>
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
            <td>{{ rating.month }}</td>
            <td>{{ rating.year }}</td>
            <td>{{ rating.office }}</td>
            <td>{{ rating.do }}</td>
            <td>{{ rating.didm }}</td>
            <td>{{ rating.di }}</td>
            <td>{{ rating.dpcr }}</td>
            <td>{{ rating.dl }}</td>
            <td>{{ rating.dhrdd }}</td>
            <td>{{ rating.dprm }}</td>
            <td>{{ rating.dictm }}</td>
            <td>{{ rating.dpl }}</td>
            <td>{{ rating.dc }}</td>
            <td>{{ rating.drd }}</td>
          </tr>
        </thead>
      </table>
      <h2 v-else>No Ratings Yet</h2>
    </div>
  </div>
  <form
    class="form"
    id="rating-form-edit2"
    :style="{ display: formVisible ? 'block' : 'none' }"
  >
    <div class="form-edit">
      <label for="">Office:</label>
      <input
        v-model="selectedRatings.office"
        type="text"
        placeholder=""
        class="input"
      />
      <label for="">Do:</label>
      <input
        v-model="selectedRatings.do"
        type="text"
        placeholder=""
        class="input"
      />
      <label for="">Didm:</label>
      <input
        v-model="selectedRatings.didm"
        type="text"
        placeholder=" Mindoro"
        class="input"
      />
    </div>
    <div class="form-edit">
      <label for="">Di:</label>
      <input
        v-model="selectedRatings.di"
        type="text"
        placeholder=" Mindoro"
        class="input"
      />
      <label for="">Dpcr:</label>
      <input
        v-model="selectedRatings.dpcr"
        type="text"
        placeholder=""
        class="input"
      />
      <label for="">Dl:</label>
      <input
        v-model="selectedRatings.dl"
        type="text"
        placeholder=""
        class="input"
      />
    </div>
    <div class="form-edit">
      <label for="">Dhrdd:</label>
      <input
        v-model="selectedRatings.dhrdd"
        type="text"
        placeholder=""
        class="input"
      />
      <label for="">Dprm:</label>
      <input
        v-model="selectedRatings.dprm"
        type="text"
        placeholder=""
        class="input"
      />
      <label for="">Dictm:</label>
      <input
        v-model="selectedRatings.dictm"
        type="text"
        placeholder=""
        class="input"
      />
    </div>
    <div class="form-edit">
      <label for="">Dpl:</label>
      <input
        v-model="selectedRatings.dpl"
        type="text"
        placeholder=""
        class="input"
      />
      <label for="">Dc:</label>
      <input
        v-model="selectedRatings.dc"
        type="text"
        placeholder=""
        class="input"
      />
      <label for="">Drd:</label>
      <input
        v-model="selectedRatings.drd"
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
        do: "",
        didm: "",
        di: "",
        dpcr: "",
        dl: "",
        dhrdd: "",
        dprm: "",
        dictm: "",
        dpl: "",
        dc: "",
        drd: "",
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
        console.log("UsersInfo:", this.UsersInfo); // Log the entire UsersInfo object
      } catch (e) {
        console.error("Error fetching UsersInfo:", e);
      }
    },

    fetchData() {
      const storedUserId = sessionStorage.getItem("id");

      if (storedUserId) {
        axios
          .get(`/viewUserRatings/${storedUserId}`)
          .then((response) => {
            this.userRatings = response.data; // Store all ratings in the array

            // Set dataFetched to true to indicate that the data is available
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
          "/api/saveUserRates",
          this.selectedRatings
        );

        if (response.status === 200) {
          const responseData = response.data;

          if (responseData && responseData.success) {
            //  console.log(responseData.message);
            this.formVisible = false;
            this.fetchData();
            this.getUsersInfo();
          } else {
            console.error("Save failed:", responseData.message);
          }
        } else {
          console.error(`Unexpected response status: ${response.status}`);
        }
      } catch (error) {
        console.error("Error saving admin:", error);
      }
    },

    closeForm() {
      this.formVisible = false;
    },
  },
};
</script>

<style>
label {
  width: 1.3rem;
}
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
</style>
