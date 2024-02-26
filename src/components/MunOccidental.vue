<template>
  <div
    class="table-data"
    :style="{ display: buttonVisible ? 'block' : 'none' }"
  >
    <div class="order">
      <div class="rating-header">
        <div>
          <h3>Unit Performance Evaluation Rating</h3>
          <h4 class="head-subtitle">Municipalities of Occidental Mindoro</h4>
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
          placeholder="Abra"
          v-model="Abra"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Calintaan"
          v-model="Calintaan"
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
          placeholder="Lubang"
          v-model="Lubang"
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
          placeholder="Mamburao"
          v-model="Mamburao"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="Paluan"
          v-model="Paluan"
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
          placeholder="Sablayan"
          v-model="Sablayan"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="SanJose"
          v-model="SanJose"
          class="rateInput"
          required
          min="0"
          max="100"
        />
        <input
          type="number"
          placeholder="SantaCruz"
          v-model="SantaCruz"
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
      formVisible: false,
      buttonVisible: true,
      storedUserId: "",
      Month: "",
      Year: "",
      Abra: "",
      Calintaan: "",
      Looc: "",
      Lubang: "",
      Magsaysay: "",
      Mamburao: "",
      Paluan: "",
      Rizal: "",
      Sablayan: "",
      SanJose: "",
      SantaCruz: "",
      componentName: "",
      saveAlert: "",
    };
  },
  components: {},
  methods: {
    async saveRating() {
      try {
        this.storedUserId = sessionStorage.getItem("id");
        const ins = await axios.post("/saveMunOcciRate", {
          UserId: this.storedUserId,
          Month: this.Month,
          Year: this.Year,
          Abra: this.Abra,
          Calintaan: this.Calintaan,
          Looc: this.Looc,
          Lubang: this.Lubang,
          Magsaysay: this.Magsaysay,
          Mamburao: this.Mamburao,
          Paluan: this.Paluan,
          Rizal: this.Rizal,
          Sablayan: this.Sablayan,
          SanJose: this.SanJose,
          SantaCruz: this.SantaCruz,
        });
        this.Month = "";
        this.Year = "";
        this.Abra = "";
        this.Calintaan = "";
        this.Looc = "";
        this.Lubang = "";
        this.Magsaysay = "";
        this.Mamburao = "";
        this.Paluan = "";
        this.Rizal = "";
        this.Sablayan = "";
        this.SanJose = "";
        this.SantaCruz = "";
        this.$emit("data-saved");
        this.saveAlert = "Successfully Rate";
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
.dim {
  position: fixed;
  display: flex;
  justify-content: center;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(
    0,
    0,
    0,
    0.5
  ); /* Adjust the last value for the desired transparency */
  z-index: 1;
  /* Make sure the overlay is above other elements */
}
.alertBox {
  position: absolute;
  background-color: white;
  height: 35%;
  width: 35%;
  top: 35%;
  left: 41%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border-radius: 2rem;
  color: var(--dark);
  gap: 1rem;
}
.checkImg {
  height: 30%;
}
.alertContent {
  color: black;
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
