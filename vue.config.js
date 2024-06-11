const { defineConfig } = require("@vue/cli-service");

module.exports = defineConfig({
  transpileDependencies: true,

  pluginOptions: {
    vuetify: {
      // https://github.com/vuetifyjs/vuetify-loader/tree/next/packages/vuetify-loader
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
