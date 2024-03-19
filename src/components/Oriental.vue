<template>
  <div v-if="visible">
    <div class="table-data">
      <div class="order">
        <div class="oriental-container">
          <form action="" id="oriental-form" @submit.prevent="save">
            <div class="rate-date-container">
              <h2>Operational Ratings</h2>
              <div class="date-container">
                Provinces :
                <select class="month" v-model="Municipality" required>
                  <option value="Occidental Mindoro PMFC">
                    Occidental Mindoro PPO
                  </option>
                  <option value="Oriental Mindoro PMFC">
                    Oriental Mindoro PPO
                  </option>
                  <option value="Marinduque PMFC">Marinduque PPO</option>
                  <option value="Romblon PMFC">Romblon PPO</option>
                  <option value="Palawan PMFC">Palawan PPO</option>
                  <option value="Puerto CMFC">Puerto Prinsesa CPO</option>
                </select>
                Month:
                <select class="month" v-model="Month" required>
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
                  class="year"
                  name="year"
                  min="2020"
                  max="2100"
                  step="1"
                  placeholder="Year"
                  v-model="Year"
                  required
                />
              </div>
            </div>
            <div class="operational-container">
              <table>
                <tr>
                  <th class="operationalHeader">ROD</th>
                  <th>RIDMD</th>
                  <th>RID</th>
                  <th>RCADD</th>
                </tr>
                <tbody>
                  <tr>
                    <td class="operationalContent">
                      <input
                        type="number"
                        name=""
                        class="ratings"
                        placeholder="167"
                        v-model="Do"
                        required
                        min="0"
                        max="167"
                      />
                    </td>
                    <td class="operationalContent">
                      <input
                        type="number"
                        name=""
                        class="ratings"
                        placeholder="166"
                        v-model="Didm"
                        required
                        min="0"
                        max="166"
                      />
                    </td>
                    <td class="operationalContent">
                      <input
                        type="number"
                        name=""
                        class="ratings"
                        placeholder="167"
                        v-model="Di"
                        required
                        min="0"
                        max="167"
                      />
                    </td>
                    <td class="operationalContent">
                      <input
                        type="number"
                        name=""
                        class="ratings"
                        placeholder="100"
                        v-model="Dpcr"
                        required
                        min="0"
                        max="100"
                      />
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <h2>Administrative Ratings</h2>
            <div class="administrative-container">
              <table>
                <tr>
                  <th>RLRDD</th>
                  <th>RLDDD</th>
                  <th>RPRMD</th>
                  <th>RICTMD</th>
                  <th>RPSMD</th>
                  <th>RCD</th>
                  <th>RRD</th>
                </tr>
                <tr>
                  <td>
                    <input
                      type="number"
                      name=""
                      class="ratings"
                      placeholder="80"
                      v-model="Dl"
                      required
                      min="0"
                      max="80"
                    />
                  </td>
                  <td>
                    <input
                      type="number"
                      name=""
                      class="ratings"
                      placeholder="80"
                      v-model="Dhrdd"
                      required
                      min="0"
                      max="80"
                    />
                  </td>
                  <td>
                    <input
                      type="number"
                      name=""
                      class="ratings"
                      placeholder="80"
                      v-model="Dprm"
                      required
                      min="0"
                      max="80"
                    />
                  </td>
                  <td>
                    <input
                      type="number"
                      name=""
                      class="ratings"
                      placeholder="80"
                      v-model="Dictm"
                      required
                      min="0"
                      max="80"
                    />
                  </td>
                  <td>
                    <input
                      type="number"
                      name=""
                      class="ratings"
                      placeholder="35"
                      v-model="Dpl"
                      required
                      min="0"
                      max="35"
                    />
                  </td>
                  <td>
                    <input
                      type="number"
                      name=""
                      class="ratings"
                      placeholder="25"
                      v-model="Dc"
                      min="0"
                      max="25"
                    />
                  </td>
                  <td>
                    <input
                      type="number"
                      name=""
                      class="ratings"
                      placeholder="20"
                      v-model="Drd"
                      required
                      min="0"
                      max="20"
                    />
                  </td>
                </tr>
              </table>
            </div>
            <div class="rating-footer">
              <button class="returnRate" @click="selectRating">Return</button>
              <button type="submit" class="submitRate">Submit</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <div v-else>
    <PpoRatingSheet></PpoRatingSheet>
  </div>
</template>

<script>
import axios from "axios";
import PpoRatingSheet from "../components/PPORatingSheet.vue";
export default {
  data() {
    return {
      Month: "",
      Year: "",
      Do: "",
      Didm: "",
      Di: "",
      Dpcr: "",
      Dl: "",
      Dhrdd: "",
      Dprm: "",
      Dictm: "",
      Dpl: "",
      Dc: "",
      Drd: "",
      Office: "Oriental Mindoro PPO",
      storedUserId: null,
      visible: true,
    };
  },

  components: {
    PpoRatingSheet,
  },

  mounted() {
    // Retrieve user information from session storage
    this.storedUserId = sessionStorage.getItem("id");
  },
  methods: {
    selectRating() {
      this.visible = false;
    },
    async save() {
      try {
        const ins = await axios.post("insertRating", {
          storedUserId: this.storedUserId,
          Month: this.Month,
          Year: this.Year,
          Do: this.Do,
          Didm: this.Didm,
          Di: this.Di,
          Dpcr: this.Dpcr,
          Dl: this.Dl,
          Dhrdd: this.Dhrdd,
          Dprm: this.Dprm,
          Dictm: this.Dictm,
          Dpl: this.Dpl,
          Dc: this.Dc,
          Drd: this.Drd,
          Office: this.Office,
        });
        (this.Month = ""), (this.Year = ""), (this.Do = "");
        this.Didm = "";
        this.Di = "";
        this.Dpcr = "";
        this.Dl = "";
        this.Dhrdd = "";
        this.Dprm = "";
        this.Dictm = "";
        this.Dpl = "";
        this.Dc = "";
        this.Drd = "";
        this.storedUserId = "";
        this.$emit("data-saved");
      } catch (e) {
        console.log(e);
      }
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
.date-container {
  display: flex;
  gap: 1rem;
}
.year,
.month {
  color: var(--dark);
  padding: 0.2rem 0.5rem;
  border-radius: 0.4rem;
}
.rate-date-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
#oriental-form {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  flex-direction: column;
}
.operational-container,
.administrative-container {
  display: flex;
  gap: 2rem;
}
.operationalTitle {
  background: rgb(226, 226, 60);
  padding: 0.5rem 1rem;
  border-radius: 1rem;
}
.ratings {
  border: 1px solid var(--dark);
  color: var(--dark);
  width: 5rem;
  padding: 0.2rem;
  border-radius: 0.4rem;
  text-align: center;
}
.rating-header {
  display: flex;
  align-items: center;
  grid-gap: 16px;
  margin-bottom: 24px;
  justify-content: center;
}
.head-subtitle {
  text-align: center;
}
.ratingsheet-container {
  display: flex;
  align-items: center;
  flex-direction: column;
  gap: 0.8rem;
}
.rate-month,
.year-rate {
  border: 1px solid var(--dark);
  padding: 0.2rem 0.5rem;
  color: var(--dark);
  background: var(--light);
  width: 16%;
}
.rateBtn {
  width: 60%;
  border: 1px solid var(--dark);
  border-radius: 0.5rem;
  padding: 0.5rem 0;
}
.rateBtn:hover {
  background-color: rgba(0, 0, 0, 0.123);
}
.submitRate {
  background: green;
  padding: 0.6rem 0.7rem;
  color: white;
  border-radius: 0.4rem;
}
.rating-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}
.returnRate {
  background: rgb(40, 93, 163);
  padding: 0.2rem 0.5rem;
  color: white;
  border-radius: 0.4rem;
}
.month,
.year {
  border: 1px solid var(--dark);
}
</style>
