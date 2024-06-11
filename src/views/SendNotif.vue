<template>
  <div>
    <button class="btn btn-primary" @click="requestPermission">
      Enable Notifications
    </button>
  </div>
</template>

<script>
import { messaging, getToken, onMessage } from "@/firebase";

export default {
  methods: {
    async requestPermission() {
      const permission = await Notification.requestPermission();
      if (permission === "granted") {
        console.log("Notification permission granted.");

        // Wait for the service worker to be ready
        const registration = await navigator.serviceWorker.ready;

        if (registration) {
          console.log("Service Worker is ready.");
          try {
            const currentToken = await getToken(messaging, {
              vapidKey:
                "BMl44ZV6cG8pDBXGieG0WYhRA0wKYSuiKC3xIR3hI2kxLJ4nfScWNZCu55G11dtYvQSiCSscFopIaRIPcG9rbWs",
              serviceWorkerRegistration: registration,
            });
            if (currentToken) {
              console.log("FCM Token:", currentToken);
              // Send the token to your server or save it in your database
            } else {
              console.log(
                "No registration token available. Request permission to generate one."
              );
            }
          } catch (err) {
            console.error("An error occurred while retrieving token. ", err);
          }
        } else {
          console.error("Service Worker is not ready.");
        }
      } else {
        console.log("Unable to get permission to notify.");
      }
    },
  },
};
</script>
