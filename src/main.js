import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import vuetify from "./plugins/vuetify";
import { loadFonts } from "./plugins/webfontloader";
import axios from "axios";
import PPO from "./components/PPO.vue";
import "bootstrap/dist/css/bootstrap.min.css";

axios.defaults.baseURL = "https://euper.infinityfreeapp.com/projectbackend/";
loadFonts();

const app = createApp(App);

app.use(router).use(vuetify).component("PPO", PPO).mount("#app");

if ("serviceWorker" in navigator) {
  navigator.serviceWorker
    .register("/firebase-messaging-sw.js")
    .then((registration) => {
      console.log("Service Worker registered with scope:", registration.scope);

      // Wait for the service worker to become active
      registration.onupdatefound = () => {
        const installingWorker = registration.installing;
        installingWorker.onstatechange = () => {
          if (installingWorker.state === "activated") {
            console.log("Service Worker activated.");
          }
        };
      };

      // If the service worker is already active, proceed
      if (registration.active) {
        console.log("Service Worker is already active.");
      }
    })
    .catch((err) => {
      console.log("Service Worker registration failed:", err);
    });
}
