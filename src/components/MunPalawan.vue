<template>
  <div
    class="table-data"
    :style="{ display: buttonVisible ? 'block' : 'none' }"
  >
    <div class="order">
      <div class="rating-header">
        <div>
          <h3>Unit Performance Evaluation Rating</h3>
          <h4 class="head-subtitle">Municipalities of Palawan</h4>
        </div>
      </div>
      <form class="ratingsheet-container" @submit.prevent="saveRating">
        <div class="rateDate">
          <select class="rateMonth" v-model="Month" required>
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
            min="2020"
            max="2100"
            step="1"
            placeholder="Year"
            v-model="Year"
            required
          />
        </div>

        <input
          type="number"
          placeholder="Aborian"
          v-model="Aborian"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Agutaya"
          v-model="Agutaya"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Arceli"
          v-model="Arceli"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Balabac"
          v-model="Balabac"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Bataraza"
          v-model="Bataraza"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="BrookesPoint"
          v-model="BrookesPoint"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Busuanga"
          v-model="Busuanga"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Cagayancilo"
          v-model="Cagayancilo"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Coron"
          v-model="Coron"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Culion"
          v-model="Culion"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Cuyo"
          v-model="Cuyo"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Dumaron"
          v-model="Dumaron"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="El Nido"
          v-model="ElNido"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Espanola"
          v-model="Espanola"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Kalayaan"
          v-model="Kalayaan"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Linapacan"
          v-model="Linapacan"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Magsaysay"
          v-model="Magsaysay"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Narra"
          v-model="Narra"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Quezon"
          v-model="Quezon"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Rizal"
          v-model="Rizal"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Roxas"
          v-model="Roxas"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="San Vicente"
          v-model="SanVicente"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Taytay"
          v-model="Taytay"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <button class="submitPPORate" type="submit">Submit</button>
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
      Aborian: "",
      Agutaya: "",
      Arceli: "",
      Balabac: "",
      Bataraza: "",
      BrookesPoint: "",
      Busuanga: "",
      Cagayancilo: "",
      Coron: "",
      Culion: "",
      Cuyo: "",
      Dumaron: "",
      ElNido: "",
      Espanola: "",
      Kalayaan: "",
      Linapacan: "",
      Magsaysay: "",
      Narra: "",
      Quezon: "",
      Rizal: "",
      Roxas: "",
      SanVicente: "",
      Taytay: "",
      componentName: "",
    };
  },
  components: {},
  methods: {
    async saveRating() {
      try {
        this.UserId = sessionStorage.getItem("id");
        const ins = await axios.post("/saveMunPalawanRate", {
          UserId: this.UserId,
          Month: this.Month,
          Year: this.Year,
          Aborian: this.Aborian,
          Agutaya: this.Agutaya,
          Arceli: this.Arceli,
          Balabac: this.Balabac,
          Bataraza: this.Bataraza,
          BrookesPoint: this.BrookesPoint,
          Busuanga: this.Busuanga,
          Cagayancilo: this.Cagayancilo,
          Coron: this.Coron,
          Culion: this.Culion,
          Cuyo: this.Cuyo,
          Dumaron: this.Dumaron,
          ElNido: this.ElNido,
          Espanola: this.Espanola,
          Kalayaan: this.Kalayaan,
          Linapacan: this.Linapacan,
          Magsaysay: this.Magsaysay,
          Narra: this.Narra,
          Quezon: this.Quezon,
          Rizal: this.Rizal,
          Roxas: this.Roxas,
          SanVicente: this.SanVicente,
          Taytay: this.Taytay,
        });
        this.Month = "";
        this.Year = "";
        this.Aborian = "";
        this.Agutaya = "";
        this.Arceli = "";
        this.Balabac = "";
        this.Bataraza = "";
        this.BrookesmPoint = "";
        this.Busuanga = "";
        this.Cagayancilo = "";
        this.Coron = "";
        this.Culion = "";
        this.Cuyo = "";
        this.Dumaron = "";
        this.ElNido = "";
        this.Espanola = "";
        this.Kalayaan = "";
        this.Linapacan = "";
        this.Magsaysay = "";
        this.Narra = "";
        this.Quezon = "";
        this.Rizal = "";
        this.Roxas = "";
        this.SanVicente = "";
        this.Taytay = "";
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
};
</script>

<style>
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
.rateMonth,
.rateYear {
  padding: 0.3rem 0.5rem;
  border: 1px solid var(--dark);
  border-radius: 0.5rem;
}
.rateDate {
  width: 60%;
  display: flex;
  justify-content: center;
  gap: 2rem;
  align-items: center;
}
.rateMonth,
.rateYear {
  padding: 0.3rem 0.5rem;
  border: 1px solid var(--dark);
  border-radius: 0.5rem;
}
.rateInput {
  width: 60%;
  border: 1px solid var(--dark);
  padding: 0.3rem 0.5rem;
  text-align: center;
  color: var(--dark);
  border-radius: 0.5rem;
}
.submitPPORate {
  background: green;
  padding: 0.5rem 1rem;
  color: white;
  border-radius: 0.5rem;
}
.backPPORate {
  background: rgb(26, 94, 182);
  padding: 0.5rem 1rem;
  color: white;
  border-radius: 0.5rem;
}
.buttonDiv {
  display: flex;
  gap: 1rem;
}
</style>
