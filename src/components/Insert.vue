<template>
  <form @submit.prevent="save">
    <label for="">ProductName: </label>
    <input
      class="inputs"
      type="text"
      placeholder="ProductName"
      v-model="ProductName"
    /><br />
    <label for="">ProductDescription: </label>
    <input
      class="inputs"
      type="text"
      placeholder="ProductDescription"
      v-model="ProductDescription"
    /><br />
    <label for="">ProductCategoryId: </label>
    <input
      class="inputs"
      type="text"
      placeholder="ProductCategoryId"
      v-model="ProductCategoryId"
    /><br />
    <label for="">ProductQuantity: </label>
    <input
      class="inputs"
      type="text"
      placeholder="ProductQuantity"
      v-model="ProductQuantity"
    /><br />
    <label for="">ProductPrice: </label>
    <input
      class="inputs"
      type="text"
      placeholder="ProductPrice"
      v-model="ProductPrice"
    /><br />
    <button class="submit" type="submit">Submit</button>
  </form>
</template>

<script>
import axios from "axios";
export default {
  data() {
    return {
      ProductName: "",
      ProductDescription: "",
      ProductCategoryId: "",
      ProductQuantity: "",
      ProductPrice: "",
    };
  },
  methods: {
    async save() {
      try {
        const ins = await axios.post("save", {
          ProductName: this.ProductName,
          ProductDescription: this.ProductDescription,
          ProductCategoryId: this.ProductCategoryId,
          ProductQuantity: this.ProductQuantity,
          ProductPrice: this.ProductPrice,
        });
        this.ProductName = "";
        this.ProductDescription = "";
        this.ProductCategoryId = "";
        this.ProductQuantity = "";
        this.ProductPrice = "";
        this.$emit("data-saved");
        this.getInfo();
      } catch (e) {
        console.log(e);
      }
    },
  },
};
</script>

<style>
.inputs,
.submit {
  border: 1px solid black;
  padding: 0.3rem;
  border-radius: 0.5rem;
}
</style>
