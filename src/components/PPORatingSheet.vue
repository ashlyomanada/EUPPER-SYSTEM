<template>
  <div
    class="table-data"
    :style="{ display: buttonVisible ? 'block' : 'none' }"
  >
    <div class="order">
      <div class="rating-header">
        <div>
          <h3>Unit Performance Evaluation Rating</h3>
          <h4 class="head-subtitle">PPO / CPO Level</h4>
        </div>
      </div>
      <div class="ratingsheet-container">
        <button class="rateBtn" @click="selectRating('Occidental')">
          Occidental Mindoro PPO
        </button>
        <button class="rateBtn" @click="selectRating('Oriental')">
          Oriental Mindoro PPO
        </button>
        <button class="rateBtn" @click="selectRating('Marinduque')">
          Marinduque PPO
        </button>
        <button class="rateBtn" @click="selectRating('Romblon')">
          Romblon PPO
        </button>
        <button class="rateBtn" @click="selectRating('Palawan')">
          Palawan PPO
        </button>
        <button class="rateBtn" @click="selectRating('PuertoPrincesa')">
          Puerto Princesa CPO
        </button>
      </div>
    </div>
  </div>
  <Occidental v-if="selectedRating === 'Occidental'" />
  <Oriental v-if="selectedRating === 'Oriental'" />
  <Marinduque v-if="selectedRating === 'Marinduque'" />
  <Romblon v-if="selectedRating === 'Romblon'" />
  <Palawan v-if="selectedRating === 'Palawan'" />
  <PuertoPrincesa v-if="selectedRating === 'PuertoPrincesa'" />
</template>

<script>
import axios from "axios";
import Oriental from "../components/Oriental.vue";
import Occidental from "../components/Occidental.vue";
import Marinduque from "../components/Marinduque.vue";
import Romblon from "../components/Romblon.vue";
import Palawan from "../components/Palawan.vue";
import PuertoPrincesa from "../components/PuertoPrincesa.vue";

export default {
  data() {
    return {
      selectedRating: null,
      buttonVisible: true,
    };
  },
  components: {
    Oriental,
    Occidental,
    Marinduque,
    Romblon,
    Palawan,
    PuertoPrincesa,
  },
  methods: {
    selectRating(rating) {
      this.selectedRating = rating;
      this.buttonVisible = false;
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
</style>
