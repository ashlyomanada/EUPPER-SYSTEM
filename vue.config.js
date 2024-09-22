const { defineConfig } = require("@vue/cli-service");

module.exports = defineConfig({
  transpileDependencies: true,

  pluginOptions: {
    vuetify: {
      // https://github.com/vuetifyjs/vuetify-loader/tree/next/packages/vuetify-loader
    },
  },

  pwa: {
    name: "EUPER",
    themeColor: "#3c91e6", // Set your desired theme color here
    msTileColor: "#000000", // Set the tile color for Microsoft apps
    appleMobileWebAppCapable: "yes",
    appleMobileWebAppStatusBarStyle: "black-translucent",
    manifestOptions: {
      background_color: "#3c91e6", // Ensure this matches your desired background color
    },
    workboxOptions: {
      skipWaiting: true, // Forces the updated service worker to take control immediately
      clientsClaim: true, // Immediately apply service worker to all active clients
    },
  },

  chainWebpack: (config) => {
    config.plugin("html").tap((args) => {
      args[0].title = "EUPER"; // Set your app name here
      args[0].favicon = "./public/logo.png"; // Path to your favicon
      return args;
    });
  },
});
