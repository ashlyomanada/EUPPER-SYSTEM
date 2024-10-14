<template>
  <div class="MPSContainer" ref="mpsContainer">
    <div ref="mpsContainer">
      <AddMPSOcci v-if="number === 0" />
      <AddMPSOrmin v-if="number === 1" />
      <AddMPSRom v-if="number === 2" />
      <AddMPSMar v-if="number === 3" />
      <AddMPSPal v-if="number === 4" />
      <AddMPSPuer v-if="number === 5" />
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
import AddMPSOcci from "../components/manageOffice/AddMPSOcci.vue";
import AddMPSOrmin from "../components/manageOffice/AddMPSOrmin.vue";
import AddMPSRom from "../components/manageOffice/AddMPSRom.vue";
import AddMPSMar from "../components/manageOffice/AddMPSMar.vue";
import AddMPSPal from "../components/manageOffice/AddMPSPal.vue";
import AddMPSPuer from "../components/manageOffice/AddMPSPuer.vue";

export default {
  data() {
    return {
      number: 0,
    };
  },
  components: {
    AddMPSOcci,
    AddMPSOrmin,
    AddMPSRom,
    AddMPSMar,
    AddMPSPal,
    AddMPSPuer,
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
