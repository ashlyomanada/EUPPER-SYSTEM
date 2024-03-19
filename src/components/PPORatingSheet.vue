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
      <Oriental v-if="selectedRating === 'Oriental'" />
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Oriental from "../components/Oriental.vue";

export default {
  data() {
    return {
      selectedRating: "Oriental",
      buttonVisible: true,
    };
  },
  components: {
    Oriental,
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
