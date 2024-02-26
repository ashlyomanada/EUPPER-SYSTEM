<template>
  <div
    class="table-data"
    :style="{ display: buttonVisible ? 'block' : 'none' }"
  >
    <div class="order">
      <div class="rating-header">
        <div>
          <h3>Unit Performance Evaluation Rating</h3>
          <h4 class="head-subtitle">Municipalities of Romblon</h4>
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
          placeholder="Alcantara"
          v-model="Alcantara"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Banton"
          v-model="Banton"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Cajidiocan"
          v-model="Cajidiocan"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Calatrava"
          v-model="Calatrava"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Concepcion"
          v-model="Concepcion"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Concuera"
          v-model="Concuera"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Ferrol"
          v-model="Ferrol"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Looc"
          v-model="Looc"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Magdiwang"
          v-model="Magdiwang"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Odiongan"
          v-model="Odiongan"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Romblon"
          v-model="Romblon"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="San Agustin"
          v-model="SanAgustin"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="San Andres"
          v-model="SanAndres"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="San Fernando"
          v-model="SanFernando"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="San Jose"
          v-model="SanJose"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Sta Fe"
          v-model="StaFe"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Sta Maria"
          v-model="StaMaria"
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
      Alcantara: "",
      Banton: "",
      Cajidiocan: "",
      Calatrava: "",
      Concepcion: "",
      Concuera: "",
      Ferrol: "",
      Looc: "",
      Magdiwang: "",
      Odiongan: "",
      Romblon: "",
      SanAgustin: "",
      SanAndres: "",
      SanFernando: "",
      SanJose: "",
      StaFe: "",
      StaMaria: "",
      componentName: "",
    };
  },
  components: {},
  methods: {
    async saveRating() {
      try {
        this.UserId = sessionStorage.getItem("id");
        const ins = await axios.post("/saveMunRomblonRate", {
          UserId: this.UserId,
          Month: this.Month,
          Year: this.Year,
          Alcantara: this.Alcantara,
          Banton: this.Banton,
          Cajidiocan: this.Cajidiocan,
          Calatrava: this.Calatrava,
          Concepcion: this.Concepcion,
          Concuera: this.Concuera,
          Ferrol: this.Ferrol,
          Looc: this.Looc,
          Magdiwang: this.Magdiwang,
          Odiongan: this.Odiongan,
          Romblon: this.Romblon,
          SanAgustin: this.SanAgustin,
          SanAndres: this.SanAndres,
          SanFernando: this.SanFernando,
          SanJose: this.SanJose,
          StaFe: this.StaFe,
          StaMaria: this.StaMaria,
        });
        this.Month = "";
        this.Year = "";
        this.Alcantara = "";
        this.Banton = "";
        this.Cajidiocan = "";
        this.Calatrava = "";
        this.Concepcion = "";
        this.Concuera = "";
        this.Ferrol = "";
        this.Looc = "";
        this.Magdiwang = "";
        this.Odiongan = "";
        this.Romblon = "";
        this.SanAgustin = "";
        this.SanAndres = "";
        this.SanFernando = "";
        this.SanJose = "";
        this.StaFe = "";
        this.StaMaria = "";
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
