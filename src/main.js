import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import vuetify from "./plugins/vuetify";
import { loadFonts } from "./plugins/webfontloader";
import axios from "axios";
import PpoRatingSheet from "./components/PPORatingSheet.vue";

// Remove the following lines, as they are for Vue 2 and are conflicting with Vue 3
// Vue.component("PpoRatingSheet", PpoRatingSheet);
// new Vue({ render: (h) => h(App), }).$mount("#app");

axios.defaults.baseURL = "http://localhost:8080/";
loadFonts();

createApp(App)
  .use(router)
  .use(vuetify)
  .component("PpoRatingSheet", PpoRatingSheet)
  .mount("#app");
