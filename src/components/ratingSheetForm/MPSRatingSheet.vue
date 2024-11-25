<template>
  <div class="MPSContainer" ref="mpsContainer">
    <div ref="mpsContainer">
      <MunOccidental v-if="number === 0" />
      <MunOriental v-if="number === 1" />
      <MunMarinduque v-if="number === 2" />
      <MunRomblon v-if="number === 3" />
      <MunPalawan v-if="number === 4" />
      <MunPrinsesa v-if="number === 5" />
    </div>
    <div class="mpsRating-btn-container">
      <button
        v-if="number > 0"
        class="nextBtn d-flex justify-content-center align-items-center gap-2"
        @click.prevent="decrease()"
      >
        <i class="fa-solid fa-arrow-left"></i>
        Previous
      </button>
      <button
        v-if="number < 5"
        class="nextBtn d-flex justify-content-center align-items-center gap-3"
        @click.prevent="increase()"
      >
        Next
        <i class="fa-solid fa-arrow-right"></i>
      </button>
    </div>
  </div>
</template>

<script>
import MunOriental from "../mpsRatingSheet/MunOriental.vue";
import MunOccidental from "../mpsRatingSheet/MunOccidental.vue";
import MunMarinduque from "../mpsRatingSheet/MunMarinduque.vue";
import MunRomblon from "../mpsRatingSheet/MunRomblon.vue";
import MunPalawan from "../mpsRatingSheet/MunPalawan.vue";
import MunPrinsesa from "../mpsRatingSheet/MunPrinsesa.vue";

export default {
  data() {
    return {
      number: 0,
      visible: true,
    };
  },
  components: {
    MunOriental,
    MunOccidental,
    MunMarinduque,
    MunRomblon,
    MunPalawan,
    MunPrinsesa,
  },

  methods: {
    increase() {
      if (this.number < 5) {
        this.number++;
        this.scrollToTop();
      }
    },
    decrease() {
      if (this.number > 0) {
        this.number--;
        this.scrollToTop();
      }
    },
    scrollToTop() {
      const container = this.$refs.mpsContainer;
      if (container) {
        container.scrollTop = 0;
      }
    },
  },
};
</script>

<style>
.MPSContainer {
  height: 100%;
  width: 100%;
  display: flex;
  flex-direction: column;
  overflow: auto;
  padding: 0rem 0rem 0.5rem 0.5rem;
}
.mpsRating-btn-container {
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 0.5rem 1rem;
  gap: 1rem;
}
.nextBtn {
  background: rgb(26, 94, 182);
  padding: 0.5rem 1rem;
  color: white;
  border-radius: 0.5rem;
  display: flex;
  right: 0;
}
</style>
