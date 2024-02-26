<template>
  <div
    class="table-data"
    :style="{ display: buttonVisible ? 'block' : 'none' }"
  >
    <div class="order">
      <div class="rating-header">
        <div>
          <h3>Unit Performance Evaluation Rating</h3>
          <h4 class="head-subtitle">Municipalities of Oriental Mindoro</h4>
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
          placeholder="Baco"
          v-model="Baco"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Bansud"
          v-model="Bansud"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Bongabong"
          v-model="Bongabong"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Bulalacao"
          v-model="Bulalacao"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Calapan"
          v-model="Calapan"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Gloria"
          v-model="Gloria"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Mansalay"
          v-model="Mansalay"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Naujan"
          v-model="Naujan"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Pinamalayan"
          v-model="Pinamalayan"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Pola"
          v-model="Pola"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="PuertoGalera"
          v-model="PuertoGalera"
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
          placeholder="SanTeodoro"
          v-model="SanTeodoro"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Socorro"
          v-model="Socorro"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Victoria"
          v-model="Victoria"
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
      storedUserId: "",
      Month: "",
      Year: "",
      Baco: "",
      Bansud: "",
      Bongabong: "",
      Bulalacao: "",
      Calapan: "",
      Gloria: "",
      Mansalay: "",
      Naujan: "",
      Pinamalayan: "",
      Pola: "",
      PuertoGalera: "",
      Roxas: "",
      SanTeodoro: "",
      Socorro: "",
      Victoria: "",
      componentName: null,
    };
  },
  components: {},
  methods: {
    async saveRating() {
      try {
        this.storedUserId = sessionStorage.getItem("id");
        const ins = await axios.post("/saveMunOrientalRate", {
          UserId: this.storedUserId,
          Month: this.Month,
          Year: this.Year,
          Baco: this.Baco,
          Bansud: this.Bansud,
          Bongabong: this.Bongabong,
          Bulalacao: this.Bulalacao,
          Calapan: this.Calapan,
          Gloria: this.Gloria,
          Mansalay: this.Mansalay,
          Naujan: this.Naujan,
          Pinamalayan: this.Pinamalayan,
          Pola: this.Pola,
          PuertoGalera: this.PuertoGalera,
          Roxas: this.Roxas,
          SanTeodoro: this.SanTeodoro,
          Socorro: this.Socorro,
          Victoria: this.Victoria,
        });
        this.Month = "";
        this.Year = "";
        this.Baco = "";
        this.Bansud = "";
        this.Bongabong = "";
        this.Bulalacao = "";
        this.Calapan = "";
        this.Gloria = "";
        this.Mansalay = "";
        this.Naujan = "";
        this.Pinamalayan = "";
        this.Pola = "";
        this.PuertoGalera = "";
        this.Roxas = "";
        this.SanTeodoro = "";
        this.Socorro = "";
        this.Victoria = "";
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
