import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import vuetify from "./plugins/vuetify";
import { loadFonts } from "./plugins/webfontloader";
import axios from "axios";
import PPO from "./components/PPO.vue";
import "bootstrap/dist/css/bootstrap.min.css";
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";

<<<<<<< HEAD
// axios.defaults.baseURL = "http://localhost:8080/";
axios.defaults.baseURL = "https://e-upper.online/backend/";
=======
axios.defaults.baseURL = "https://e-upper.online/backend/";
axios.defaults.headers.common["Access-Control-Allow-Origin"] = "*";
>>>>>>> 65fa85e1ad0a2ac0cc5b6c011d29033118f1499f
loadFonts();

const app = createApp(App);

app.use(Toast, {
  timeout: 3000, // Default timeout of 3 seconds
});
app.use(router).use(vuetify).component("PPO", PPO).mount("#app");

if ("serviceWorker" in navigator) {
  navigator.serviceWorker
    .register("/firebase-messaging-sw.js")
    .then((registration) => {
      // console.log("Service Worker registered with scope:", registration.scope);

      // Wait for the service worker to become active
      registration.onupdatefound = () => {
        const installingWorker = registration.installing;
        installingWorker.onstatechange = () => {
          if (installingWorker.state === "activated") {
            // console.log("Service Worker activated.");
          }
        };
      };

      // If the service worker is already active, proceed
      if (registration.active) {
        // console.log("Service Worker is already active.");
      }
    })
    .catch((err) => {
      // console.log("Service Worker registration failed:", err);
    });
}
