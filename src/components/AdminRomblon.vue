<template>
  <div class="table-data">
    <div class="order">
      <div class="head">
        <div class="head-options">
          <div style="text-align: center">
            <h3>Unit Performance Evaluation Rating</h3>
            <h4>Romblon MPS / CPS Level</h4>
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
            <th class="t-row">Alcantara</th>
            <th class="t-row">Banton</th>
            <th class="t-row">Cajidiocan</th>
            <th class="t-row">Calatrava</th>
            <th class="t-row">Concepcion</th>
            <th class="t-row">Concuera</th>
            <th class="t-row">Ferrol</th>
            <th class="t-row">Looc</th>
            <th class="t-row">Magdiwang</th>
            <th class="t-row">Odiongan</th>
            <th class="t-row">Romblon</th>
            <th class="t-row">SanAgustin</th>
            <th class="t-row">SanAndres</th>
            <th class="t-row">SanFernando</th>
            <th class="t-row">SanJose</th>
            <th class="t-row">StaFe</th>
            <th class="t-row">StaMaria</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="rating in usersRate" :key="rating.id">
            <td class="t-data">{{ rating.month }}</td>
            <td class="t-data">{{ rating.year }}</td>
            <td class="t-data">{{ rating.alcantara }}</td>
            <td class="t-data">{{ rating.banton }}</td>
            <td class="t-data">{{ rating.cajidiocan }}</td>
            <td class="t-data">{{ rating.calatrava }}</td>
            <td class="t-data">{{ rating.concepcion }}</td>
            <td class="t-data">{{ rating.concuera }}</td>
            <td class="t-data">{{ rating.ferrol }}</td>
            <td class="t-data">{{ rating.looc }}</td>
            <td class="t-data">{{ rating.magdiwang }}</td>
            <td class="t-data">{{ rating.odiongan }}</td>
            <td class="t-data">{{ rating.romblon }}</td>
            <td class="t-data">{{ rating.san_agustin }}</td>
            <td class="t-data">{{ rating.san_andres }}</td>
            <td class="t-data">{{ rating.san_fernando }}</td>
            <td class="t-data">{{ rating.san_jose }}</td>
            <td class="t-data">{{ rating.sta_fe }}</td>
            <td class="t-data">{{ rating.sta_maria }}</td>
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
        const response = await axios.get(`/getRomRates`);
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
