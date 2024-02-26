<template>
  <div
    class="table-data"
    :style="{ display: buttonVisible ? 'block' : 'none' }"
  >
    <div class="order">
      <div class="rating-header">
        <div>
          <h3>Unit Performance Evaluation Rating</h3>
          <h4 class="head-subtitle">RMFB / PMFC Level</h4>
        </div>
      </div>
      <form @submit.prevent="saveRating" class="ratingsheet-container">
        <div class="rateDate">
          <select class="rateMonth" name="month" v-model="Month" required>
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
            name="year"
            min="2000"
            max="2100"
            step="1"
            placeholder="Year"
            v-model="Year"
            required
          />
        </div>
        <input
          type="number"
          class="rateInput"
          placeholder="Regional Mobile Office Battalion"
          v-model="Regional"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          class="rateInput"
          placeholder=" Occidental Mindoro PMFC"
          v-model="Occidental"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          class="rateInput"
          placeholder="Oriental Mindoro PMFC"
          v-model="Oriental"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          class="rateInput"
          placeholder="Marinduque PMFP"
          v-model="Marinduque"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          class="rateInput"
          placeholder="Romblon PMFC"
          v-model="Romblon"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          class="rateInput"
          placeholder="Palawan PMFC"
          v-model="Palawan"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          class="rateInput"
          placeholder="Puerto Princesa CMFC"
          v-model="PuertoPrinsesa"
          required
          min="0"
          max="100"
        />
        <button class="submitPPORate">Submit</button>
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
      Regional: "",
      Occidental: "",
      Oriental: "",
      Marinduque: "",
      Romblon: "",
      Palawan: "",
      PuertoPrinsesa: "",
    };
  },

  methods: {
    async saveRating() {
      try {
        this.UserId = sessionStorage.getItem("id");
        const ins = await axios.post("/saveRMFBRate", {
          UserId: this.UserId,
          Month: this.Month,
          Year: this.Year,
          Regional: this.Regional,
          Occidental: this.Occidental,
          Oriental: this.Oriental,
          Marinduque: this.Marinduque,
          Romblon: this.Romblon,
          Palawan: this.Palawan,
          PuertoPrinsesa: this.PuertoPrinsesa,
        });
        this.UserId = "";
        this.Month = "";
        this.Year = "";
        this.Regional = "";
        this.Occidental = "";
        this.Oriental = "";
        this.Marinduque = "";
        this.Romblon = "";
        this.Palawan = "";
        this.PuertoPrinsesa = "";
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
.rate-month,
.year-rate {
  border: 1px solid var(--dark);
  padding: 0.2rem 0.5rem;
  color: var(--dark);
  background: var(--light);
  width: 16%;
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
</style>
