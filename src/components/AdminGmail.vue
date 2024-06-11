<template>
  <div class="table-data">
    <div class="order">
      <div class="head">
        <h3>Announcement To All Users</h3>
      </div>
      <form class="form" id="form" @submit.prevent="sendSms">
        <label for="">To:</label>
        <input type="text" placeholder="All User" class="input" readonly />
        <label for="">From:</label>
        <input
          type="text"
          placeholder="PRO MIMAROPA Announcement"
          class="input"
          value="PRO MIMAROPA Announcement"
          readonly
        />
        <textarea
          placeholder="Type message"
          v-model="messageContent"
        ></textarea>
        <button type="submit">Send</button>
      </form>
    </div>
  </div>
  <div class="modalBg" v-if="formVisible">
    <div class="alertBox">
      <img class="checkImg" src="./img/check2.gif" alt="" />
      <h1 class="alertContent">Successfully Send</h1>
      <button class="btn btn-primary" @click="closeForm">Okay</button>
    </div>
  </div>
</template>

<script>
import axios from "axios";
export default {
  data() {
    return {
      messageContent: "",
      formVisible: false,
    };
  },

  methods: {
    async sendSms() {
      try {
        const messageContent = this.messageContent; // Access messageContent directly

        this.sendingInProgress = true;
        const response = await axios.post("/sendSMSToAllUser", {
          message: messageContent,
        });

        if (response.status === 200) {
          console.log("SMS sent successfully.");
          this.formVisible2 = false; // Hide the SMS form after sending
          this.messageContent = "";
          this.formVisible = true;
          setTimeout(() => {
            this.formVisible = false;
          }, 5000);
        } else {
          console.error("Failed to send SMS.");
        }
      } catch (error) {
        console.error("Error sending SMS:", error);
      } finally {
        // Set sendingInProgress to false once the process completes
        this.sendingInProgress = false;
      }
    },
    closeForm() {
      this.formVisible = false;
    },
  },
};
</script>

<style>
.form {
  position: relative;
  display: flex;
  align-items: flex-start;
  flex-direction: column;
  gap: 10px;
  width: 100%;
  background: var(--light);
  padding: 20px;
  border-radius: 10px;
}

.form .title {
  color: royalblue;
  font-size: 30px;
  font-weight: 600;
  letter-spacing: -1px;
  line-height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.form input {
  outline: 0;
  border: 1px solid rgb(219, 213, 213);
  padding: 8px 14px;
  border-radius: 8px;
  width: 100%;
  height: 50px;
  color: var(--dark);
}

.form textarea {
  border-radius: 8px;
  height: 100px;
  width: 100%;
  resize: none;
  outline: 0;
  padding: 8px 14px;
  border: 1px solid rgb(219, 213, 213);
  color: var(--dark);
}

.form button {
  align-self: flex-end;
  padding: 8px;
  outline: 0;
  border: 0;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  background-color: royalblue;
  color: #fff;
  cursor: pointer;
}
@media screen and (max-width: 600px) {
  .form {
    width: 100vw;
  }
}
</style>
