<template>
  <div class="MPSContainer">
    <div>
      <Occidental v-if="number === 0" />
      <Oriental v-if="number === 1" />
      <Marinduque v-if="number === 2" />
      <Romblon v-if="number === 3" />
      <Palawan v-if="number === 4" />
      <PuertoPrincesa v-if="number === 5" />
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
import Oriental from "../components/Oriental.vue";
import Occidental from "../components/Occidental.vue";
import Marinduque from "../components/Marinduque.vue";
import Romblon from "../components/Romblon.vue";
import Palawan from "../components/Palawan.vue";
import PuertoPrincesa from "../components/PuertoPrincesa.vue";

export default {
  data() {
    return {
      number: 0,
      visible: true,
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
    increase() {
      if (this.number < 5) {
        this.number++;
      }
    },
    decrease() {
      if (this.number > 0) {
        this.number--;
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
}
.mps-btn-container {
  width: 100%;
  display: flex;
  justify-content: flex-end;
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
