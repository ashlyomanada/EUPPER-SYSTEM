<template>
  <div class="table-data">
    <div class="order">
      <div class="head">
        <div class="head-options">
          <div style="text-align: center">
            <h3>Unit Performance Evaluation Rating</h3>
            <h4>Palawan MPS / CPS Level</h4>
          </div>
          <div class="date-options">
            <div>
              Select Month:<select class="month" name="month">
                <option value="1">January</option>
                <option value="2">February</option>
                <option value="3">March</option>
                <option value="4">April</option>
                <option value="5">May</option>
                <option value="6">June</option>
                <option value="7">July</option>
                <option value="8">August</option>
                <option value="9">September</option>
                <option value="10">October</option>
                <option value="11">November</option>
                <option value="12">December</option>
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
              />
              <button class="find"><i class="bx bx-search"></i>Find</button>
            </div>
            <button class="generate">Generate Excel Report</button>
            <button class="generate" @click="generatePdf">
              Generate Pdf Report
            </button>
          </div>
        </div>
      </div>
      <table>
        <thead>
          <tr>
            <th class="t-row">Month</th>
            <th class="t-row">Year</th>
            <th class="t-row">Aborian</th>
            <th class="t-row">Agutaya</th>
            <th class="t-row">Arceli</th>
            <th class="t-row">Balabac</th>
            <th class="t-row">Bataraza</th>
            <th class="t-row">BrookesPoint</th>
            <th class="t-row">Busuanga</th>
            <th class="t-row">Cagayancilo</th>
            <th class="t-row">Coron</th>
            <th class="t-row">Culion</th>
            <th class="t-row">Cuyo</th>
            <th class="t-row">Dumaron</th>
            <th class="t-row">ElNido</th>
            <th class="t-row">Espanola</th>
            <th class="t-row">Kalayaan</th>
            <th class="t-row">Linapacan</th>
            <th class="t-row">Magsaysay</th>
            <th class="t-row">Narra</th>
            <th class="t-row">Quezon</th>
            <th class="t-row">Rizal</th>
            <th class="t-row">Roxas</th>
            <th class="t-row">SanVicente</th>
            <th class="t-row">Taytay</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="rating in usersRate" :key="rating.id">
            <td class="t-data">{{ rating.month }}</td>
            <td class="t-data">{{ rating.year }}</td>
            <td class="t-data">{{ rating.aborian }}</td>
            <td class="t-data">{{ rating.agutaya }}</td>
            <td class="t-data">{{ rating.arceli }}</td>
            <td class="t-data">{{ rating.balabac }}</td>
            <td class="t-data">{{ rating.bataraza }}</td>
            <td class="t-data">{{ rating.brookes_point }}</td>
            <td class="t-data">{{ rating.busuanga }}</td>
            <td class="t-data">{{ rating.cagayancilo }}</td>
            <td class="t-data">{{ rating.coron }}</td>
            <td class="t-data">{{ rating.culion }}</td>
            <td class="t-data">{{ rating.cuyo }}</td>
            <td class="t-data">{{ rating.dumaron }}</td>
            <td class="t-data">{{ rating.el_nido }}</td>
            <td class="t-data">{{ rating.espanola }}</td>
            <td class="t-data">{{ rating.kalayaan }}</td>
            <td class="t-data">{{ rating.linapacan }}</td>
            <td class="t-data">{{ rating.magsaysay }}</td>
            <td class="t-data">{{ rating.narra }}</td>
            <td class="t-data">{{ rating.quezon }}</td>
            <td class="t-data">{{ rating.rizal }}</td>
            <td class="t-data">{{ rating.roxas }}</td>
            <td class="t-data">{{ rating.san_vicente }}</td>
            <td class="t-data">{{ rating.taytay }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      usersRate: "",
      dataFetched: false,
      visible: true,
    };
  },
  components: {},
  mounted() {
    this.fetchUserData();
  },
  methods: {
    async fetchUserData() {
      try {
        const response = await axios.get(`/getPalRates`);
        this.usersRate = response.data;
        this.dataFetched = true;
        // console.log(this.usersRate);
      } catch (e) {
        console.log(e);
      }
    },
  },
};
</script>

<style>
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
