<template>
  <div
    class="table-data"
    :style="{ display: visibleMunicipal ? 'block' : 'none' }"
  >
    <div class="order">
      <div class="rating-header">
        <div>
          <h3>Unit Performance Evaluation Rating</h3>
          <h4 class="head-subtitle">MPS / CPS Level</h4>
        </div>
      </div>
      <div class="ratingsheet-container">
        <button class="rateBtn" @click="selectedRating('MunOccidental')">
          Occidental Mindoro
        </button>
        <button class="rateBtn" @click="selectedRating('MunOriental')">
          Oriental Mindoro
        </button>
        <button class="rateBtn" @click="selectedRating('MunMarinduque')">
          Marinduque
        </button>
        <button class="rateBtn" @click="selectedRating('MunRomblon')">
          Romblon
        </button>
        <button class="rateBtn" @click="selectedRating('MunPalawan')">
          Palawan
        </button>
        <button class="rateBtn" @click="selectedRating('MunPrincesa')">
          Puerto Princesa
        </button>
      </div>
    </div>
  </div>
  <MunOccidental v-if="selectedComponent === 'MunOccidental'"></MunOccidental>
  <MunOriental v-if="selectedComponent === 'MunOriental'"></MunOriental>
  <MunPalawan v-if="selectedComponent === 'MunPalawan'"></MunPalawan>
  <MunPrincesa v-if="selectedComponent === 'MunPrincesa'"></MunPrincesa>
  <MunRomblon v-if="selectedComponent === 'MunRomblon'"></MunRomblon>
  <MunMarinduque v-if="selectedComponent === 'MunMarinduque'"></MunMarinduque>
</template>

<script>
import MunOccidental from "../components/MunOccidental.vue";
import MunOriental from "../components/MunOriental.vue";
import MunPalawan from "../components/MunPalawan.vue";
import MunPrincesa from "../components/MunPrincesa.vue";
import MunRomblon from "../components/MunRomblon.vue";
import MunMarinduque from "../components/MunMarinduque.vue";
export default {
  components: {
    MunOccidental,
    MunOriental,
    MunPalawan,
    MunPrincesa,
    MunRomblon,
    MunMarinduque,
  },

  data() {
    return {
      selectedComponent: "",
      visibleMunicipal: true,
    };
  },

  methods: {
    selectedRating(rating) {
      this.selectedComponent = rating;
      this.visibleMunicipal = false;
    },
  },
};
</script>

<style></style>
