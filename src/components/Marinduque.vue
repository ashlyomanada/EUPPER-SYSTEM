<template>
  <div class="table-data">
    <div class="order">
      <div class="rating-header">
        <div>
          <h3>Marinduque PPO Ratings</h3>
          <h4 class="head-subtitle">PPO / CPO Level</h4>
        </div>
      </div>
      <div class="oriental-container">
        <form action="" id="oriental-form" @submit.prevent="save">
          <div class="rate-date-container">
            <h1>Operational Ratings</h1>
            <div class="date-container">
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
                min="2000"
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
                <th>DO / 167</th>
                <th>DIDM / 166</th>
                <th>DI / 167</th>
                <th>DPCR / 100</th>
              </tr>
              <tbody>
                <tr>
                  <td>
                    <input
                      type="number"
                      name=""
                      class="ratings"
                      placeholder="DO"
                      v-model="Do"
                      required
                    />
                  </td>
                  <td>
                    <input
                      type="number"
                      name=""
                      class="ratings"
                      placeholder="DIDM"
                      v-model="Didm"
                      required
                    />
                  </td>
                  <td>
                    <input
                      type="number"
                      name=""
                      class="ratings"
                      placeholder="DI"
                      v-model="Di"
                      required
                    />
                  </td>
                  <td>
                    <input
                      type="number"
                      name=""
                      class="ratings"
                      placeholder="DPCR"
                      v-model="Dpcr"
                      required
                    />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <h1>Administrative Ratings</h1>
          <div class="administrative-container">
            <table>
              <tr>
                <th>Dl / 80</th>
                <th>Dhrdd / 80</th>
                <th>Dprm / 80</th>
                <th>Dictm / 80</th>
                <th>Dpl / 35</th>
                <th>Dc / 25</th>
                <th>Drd / 20</th>
              </tr>
              <tr>
                <td>
                  <input
                    type="number"
                    name=""
                    class="ratings"
                    placeholder="Dl"
                    v-model="Dl"
                    required
                  />
                </td>
                <td>
                  <input
                    type="number"
                    name=""
                    class="ratings"
                    placeholder="Dhrdd"
                    v-model="Dhrdd"
                    required
                  />
                </td>
                <td>
                  <input
                    type="number"
                    name=""
                    class="ratings"
                    placeholder="Dprm"
                    v-model="Dprm"
                    required
                  />
                </td>
                <td>
                  <input
                    type="number"
                    name=""
                    class="ratings"
                    placeholder="Dictm"
                    v-model="Dictm"
                    required
                  />
                </td>
                <td>
                  <input
                    type="number"
                    name=""
                    class="ratings"
                    placeholder="Dpl"
                    v-model="Dpl"
                    required
                  />
                </td>
                <td>
                  <input
                    type="number"
                    name=""
                    class="ratings"
                    placeholder="Dc"
                    v-model="Dc"
                    required
                  />
                </td>
                <td>
                  <input
                    type="number"
                    name=""
                    class="ratings"
                    placeholder="Drd"
                    v-model="Drd"
                    required
                  />
                </td>
              </tr>
            </table>
          </div>
          <div class="rating-footer">
            <button type="submit" class="submitRate">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

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
      Office: "Marinduque CPO",
      storedUserId: null,
    };
  },
  mounted() {
    // Retrieve user information from session storage
    this.storedUserId = sessionStorage.getItem("id");
  },
  methods: {
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
.ratings {
  border: 1px solid var(--dark);
  color: var(--dark);
  width: 5rem;
  padding: 0.2rem;
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
  padding: 0.2rem 0;
}
.submitRate {
  background: green;
  padding: 0.2rem 0.5rem;
  color: white;
}
.rating-footer {
  display: flex;
  justify-content: flex-end;
}
.month,
.year {
  border: 1px solid var(--dark);
}
</style>
