<template>
  <div class="table-data">
    <div class="order">
      <div class="rating-header">
        <div>
          <h2>Unit Performance Evaluation Rating</h2>
          <h4 class="head-subtitle">PPO / CPO Level</h4>
        </div>
      </div>
      <form class="form" @submit.prevent="insertColumn">
        <input
          type="text"
          placeholder="New Office Name"
          v-model="newColumnName"
          class="input"
          required
        />
        <button type="submit">Add PPO Office</button>
      </form>
    </div>
  </div>
  <div class="modalBg" v-if="formVisible">
    <div class="alertBox">
      <img class="checkImg" src="./img/check2.gif" alt="" />
      <h4 class="alertContent">Successfully Added New Office</h4>
      <button class="btn btn-primary" @click="okayBtn">Okay</button>
    </div>
  </div>
</template>

<script>
import axios from "axios";
export default {
  data() {
    return {
      newColumnName: "",
      formVisible: false,
    };
  },

  methods: {
    async insertColumn() {
      try {
        const columnName = this.newColumnName.trim();
        const sanitizedColumnName = columnName.replace(/\s+/g, "_");
        const ins = await axios.post("/addColumnPPO", {
          columnName: sanitizedColumnName,
        });
        // Clear the input field after successfully adding the column
        this.newColumnName = "";
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
.modalBg {
  height: 100vh;
  width: 100%;
  position: absolute;
  top: 0;
  left: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(70, 70, 70, 0.473);
}
.alertBox {
  height: 30%;
  width: 40%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  background-color: white;
  gap: 0.5rem;
  border-radius: 1rem;
}
.checkImg {
  height: 4rem;
}
</style>
