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
    <div class="mps-btn-container">
      <button v-if="number > 0" class="nextBtn" @click.prevent="decrease()">
        Previous
      </button>
      <button v-if="number < 5" class="nextBtn" @click.prevent="increase()">
        Next
      </button>
    </div>
  </div>
</template>

<script>
import Occidental from "../components/Occidental.vue";
import Oriental from "../components/Oriental.vue";
import Marinduque from "../components/Marinduque.vue";
import Romblon from "../components/Romblon.vue";
import Palawan from "../components/Palawan.vue";
import PuertoPrincesa from "../components/PuertoPrincesa.vue";
export default {
  data() {
    return {
      number: 0,
    };
  },
  components: {
    Occidental,
    Oriental,
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
.month,
.year,
.find,
.generate {
  border: 1px solid var(--dark);
  padding: 0.2rem 0.5rem;
}
.head-options {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  width: 100%;
}
.date-options {
  width: 100%;
  display: flex;
  justify-content: space-between;
}
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
