<template>
  <div id="app">
    <v-app>
      <router-view> </router-view>
    </v-app>
  </div>
</template>
<script>
import Cookies from "js-cookie";

export default {
  name: "App",

  data() {
    return {
      deferredPrompt: null,
      isActive: false,
      menuImage: require("@/assets/more.png"),
    };
  },

  methods: {
    toggleMenu() {
      this.isActive = !this.isActive;
      this.menuImage = this.isActive
        ? require("@/assets/close.png")
        : require("@/assets/more.png");
    },
  },
};

/*  created() {
    window.addEventListener("beforeinstallprompt", (e) => {
      e.preventDefault();

      // Stash the event so it can be triggered later.

      this.deferredPrompt = e;

      if (Cookies.get("add-to-home-screen") === undefined) {
        this.deferredPrompt = e;
      }
    });

    window.addEventListener("appinstalled", () => {
      this.deferredPrompt = null;
    });
  },

  methods: {
    async dismiss() {
      Cookies.set("add-to-home-screen", null, { expires: 15 });

      this.deferredPrompt = null;

      this.deferredPrompt = null;
    },

    async install() {
      this.deferredPrompt.prompt();
    },

    toggleMenu() {
      this.isActive = !this.isActive;
    },
  },*/
</script>
