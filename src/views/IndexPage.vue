<template>
  <div class="container">
    <Insert @data-saved="getInfo" />
    <table border="1">
      <tr>
        <td>ProductName</td>
        <td>ProductDescription</td>
        <td>ProductPrice</td>
        <td>ProductQuantity</td>
        <td>Action</td>
      </tr>
      <tr v-for="info in info" :key="info.ProductId">
        <td>{{ info.ProductName }}</td>
        <td>{{ info.ProductDescription }}</td>
        <td>{{ info.ProductPrice }}</td>
        <td>{{ info.ProductQuantity }}</td>
        <td><button @click="deleteProduct(info.ProductId)">Delete</button></td>
      </tr>
    </table>
  </div>
</template>
<style scoped>
.container {
  height: 100vh;
  width: 100vw;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 3rem;
}
</style>
<script>
import axios from "axios";
import Insert from "../components/Insert.vue";
export default {
  components: {
    Insert,
  },
  data() {
    return {
      info: [],
      ProductName: "",
      ProductDescription: "",
      ProductCategoryId: "",
      ProductQuantity: "",
      ProductPrice: "",
    };
  },
  created() {
    this.getInfo();
  },
  methods: {
    async deleteProduct(recordId) {
      const confirm = window.confirm(
        "Are you sure you want to delete this record?"
      );
      if (confirm) {
        await axios.post("del", {
          id: recordId,
        });
        this.getInfo();
      }
    },
    async getInfo() {
      try {
        const info = await axios.get("getData");
        this.info = info.data;
      } catch (e) {
        console.log(e);
      }
    },
  },
};
</script>
