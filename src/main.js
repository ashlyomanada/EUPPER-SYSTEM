import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import vuetify from "./plugins/vuetify"; // Import Vuetify instance
import { loadFonts } from "./plugins/webfontloader";
import axios from "axios";
import PPO from "./components/PPO.vue"; // Import your PPO component
import "bootstrap/dist/css/bootstrap.min.css";

axios.defaults.baseURL = "http://localhost:8080/";
loadFonts();

createApp(App)
  .use(router)
  .use(vuetify) // Use Vuetify
  .component("PPO", PPO) // Register the PPO component
  .mount("#app");
