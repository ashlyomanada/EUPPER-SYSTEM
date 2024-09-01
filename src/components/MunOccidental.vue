<template>
  <div class="table-data" :style="{ display: visible ? 'block' : 'none' }">
    <div class="order">
      <div class="rating-header">
        <div class="rating-subheader">
          <h3>Unit Performance Evaluation Rating</h3>
          <h4 class="head-subtitle">Municipalities of Occidental Mindoro</h4>
        </div>
      </div>

      <div class="oriental-container">
        <form action="" id="oriental-form" @submit.prevent="saveRating">
          <div class="rate-date-container">
            <h2>Operational Ratings</h2>

            <div class="date-container">
              Municipality :
              <select class="month" v-model="Municipality" required>
                <option value="Abra">Abra</option>
                <option value="Calintaan">Calintaan</option>
                <option value="Looc">Looc</option>
                <option value="Lubang">Lubang</option>
                <option value="Magsaysay">Magsaysay</option>
                <option value="Mamburao">Mamburao</option>
                <option value="Paluan">Paluan</option>
                <option value="Rizal">Rizal</option>
                <option value="Sablayan">Sablayan</option>
                <option value="SanJose">SanJose</option>
                <option value="SantaCruz">SantaCruz</option>
              </select>
              Month :
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
                      v-model="ROD"
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
                      v-model="RIDMD"
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
                      v-model="RID"
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
                      v-model="RCADD"
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
                    v-model="RLRDD"
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
                    v-model="RLDDD"
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
                    v-model="RPRMD"
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
                    v-model="RICTMD"
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
                    v-model="RPSMD"
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
                    v-model="RCD"
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
                    v-model="RRD"
                    required
                    min="0"
                    max="20"
                  />
                </td>
              </tr>
            </table>
          </div>
          <div class="rating-footer">
            <button type="submit" class="submitRate">Submit</button>
          </div>
        </form>
        <div :class="{ dim: formVisible }">
          <div class="alertBox" v-if="formVisible">
            <img class="checkImg" src="./img/check2.gif" alt="" />
            <h1 class="alertContent">Successfully Rated</h1>
            <button class="backPPORate" @click="okayBtn">Okay</button>
          </div>
        </div>
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
      ROD: "",
      RIDMD: "",
      RID: "",
      RCADD: "",
      RLRDD: "",
      RLDDD: "",
      RPRMD: "",
      RICTMD: "",
      RPSMD: "",
      RCD: "",
      RRD: "",
      Municipality: "",
      storedUserId: null,
      visible: true,
      formVisible: false,
    };
  },
  mounted() {
    this.storedUserId = sessionStorage.getItem("id");
  },
  methods: {
    back() {
      this.visible = false;
    },

    async saveRating() {
      try {
        const ins = await axios.post("insertMpsOcci", {
          storedUserId: this.storedUserId,
          Month: this.Month,
          Year: this.Year,
          Office: this.Municipality,
          Rod: this.ROD,
          Ridmd: this.RIDMD,
          Rid: this.RID,
          Rcadd: this.RCADD,
          Rlrdd: this.RLRDD,
          Rlddd: this.RLDDD,
          Rprmd: this.RPRMD,
          Rictmd: this.RICTMD,
          Rpsmd: this.RPSMD,
          Rcd: this.RCD,
          Rrd: this.RRD,
        });
        this.Month = "";
        this.Year = "";
        this.ROD = "";
        this.RIDMD = "";
        this.RID = "";
        this.RCADD = "";
        this.RLRDD = "";
        this.RLDDD = "";
        this.RPRMD = "";
        this.RICTMD = "";
        this.RPSMD = "";
        this.RCD = "";
        this.RRD = "";
        this.Municipality = "";
        this.$emit("data-saved");
        this.formVisible = true;
        setTimeout(() => {
          this.formVisible = false;
        }, 5000);
      } catch (e) {
        console.log(e);
      }
    },

    okayBtn() {
      this.formVisible = false;
    },
  },
  components: {},
};
</script>

<style>
.backBtn {
  background: rgb(40, 93, 163);
  color: white;
  border: none;
  border-radius: 0.5rem;
  padding: 0.5rem 0;
}
.backBtn:hover {
  background: rgb(65, 130, 216);
}
</style>
