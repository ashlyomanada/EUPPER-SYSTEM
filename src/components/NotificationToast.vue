<!-- NotificationToast.vue -->
<template>
  <div style="font-size: 0.8rem">
    <p class="notif-info">
      {{ title }} , <br />
      {{ message }}
    </p>
    <button class="notif-btn" @click.prevent="updateNotif">Mark as read</button>
  </div>
</template>

<script>
import axios from "axios";
export default {
  props: {
    id: BigInt,
    title: String,
    message: String,
  },

  methods: {
    async updateNotif() {
      try {
        const response = await axios.post("updateNotifications", {
          Id: parseInt(this.id),
        });
      } catch (e) {
        console.log(e);
      }
    },
  },
};
</script>

<style>
.notif-btn:hover {
  color: var(--dark-grey);
  line-height: 10px;
}

.notif-info {
  margin-bottom: 0.5rem;
  text-align: justify;
}
</style>
