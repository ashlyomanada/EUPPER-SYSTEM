<template>
  <div>
    <h1>Push Notification Example</h1>
    <button class="btn btn-primary" @click="subscribeToNotifications">
      Subscribe to Notifications
    </button>
    <button class="btn btn-primary" @click="sendTestNotification">
      Send Test Notification
    </button>
  </div>
</template>

<script>
export default {
  methods: {
    async subscribeToNotifications() {
      try {
        if (!("serviceWorker" in navigator)) {
          alert("Service workers are not supported in this browser.");
          return;
        }

        const registration = await navigator.serviceWorker.register("/sw.js");

        // Check for existing subscriptions
        const existingSubscription =
          await registration.pushManager.getSubscription();
        if (existingSubscription) {
          // Unsubscribe the old subscription
          await existingSubscription.unsubscribe();
          console.log("Unsubscribed from the existing subscription.");
        }

        // Create a new subscription
        const subscription = await registration.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: this.urlBase64ToUint8Array(
            "BEliJagvFtxEns4lZSerBLv2Vu2HyfXwQSnZZ1sZgUyzRqLmgv4OiB8xhqgK2Vdoi0LS3JlD1varnue7Y5kB5Tg"
          ),
        });

        // Save the subscription to the server
        await fetch("http://localhost:8080/push/save-subscription", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(subscription),
        });

        alert("Subscribed successfully!");
      } catch (err) {
        console.error("Error subscribing to notifications:", err);
      }
    },

    async sendTestNotification() {
      try {
        const response = await fetch(
          "http://localhost:8080/push/send-notification",
          {
            method: "POST",
          }
        );

        if (!response.ok) {
          throw new Error(`Server error: ${response.statusText}`);
        }

        const result = await response.json();
        if (result.success) {
          alert("Notification sent!");
        } else {
          throw new Error("Notification failed to send.");
        }
      } catch (err) {
        console.error("Error sending notification:", err);
        alert(
          "Failed to send notification. Check the console for more details."
        );
      }
    },
    urlBase64ToUint8Array(base64String) {
      const padding = "=".repeat((4 - (base64String.length % 4)) % 4);
      const base64 = (base64String + padding)
        .replace(/-/g, "+")
        .replace(/_/g, "/");
      const rawData = window.atob(base64);
      return new Uint8Array([...rawData].map((char) => char.charCodeAt(0)));
    },
  },
};
</script>
