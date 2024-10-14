<template>
  <div class="MPSContainer">
    <div>
      <AdminOccidental v-if="number === 0" />
      <AdminOriental v-if="number === 1" />
      <AdminMarinduque v-if="number === 2" />
      <AdminRomblon v-if="number === 3" />
      <AdminPalawan v-if="number === 4" />
      <AdminPuerto v-if="number === 5" />
    </div>
    <div class="mps-btn-container">
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
import AdminOccidental from "../components/AdminOccidental.vue";
import AdminOriental from "../components/AdminOriental.vue";
import AdminMarinduque from "../components/AdminMarinduque.vue";
import AdminRomblon from "../components/AdminRomblon.vue";
import AdminPalawan from "../components/AdminPalawan.vue";
import AdminPuerto from "../components/AdminPuerto.vue";
export default {
  data() {
    return {
      number: 0,
    };
  },
  watch: {
    number: function (newComponent) {
      // Update document title based on the selected component

      switch (newComponent) {
        case 0:
          document.title = "EUPER - AdminOccidental";
          break;

        case 1:
          document.title = "EUPER - AdminOriental";
          break;
        case 2:
          document.title = "EUPER - AdminMarinduque";
          break;
        case 3:
          document.title = "EUPER - AdminRomblon";
          break;
        case 4:
          document.title = "EUPER - AdminPalawan";
          break;
        case 5:
          document.title = "EUPER - AdminPuerto";
          break;
      }
    },
  },
  components: {
    AdminOccidental,
    AdminOriental,
    AdminMarinduque,
    AdminRomblon,
    AdminPalawan,
    AdminPuerto,
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
