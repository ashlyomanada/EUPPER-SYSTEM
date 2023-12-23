<template>
  <div class="about">
    <h1>This is a Manage Page</h1>
  </div>
  <div id="app">
    <v-app>
      <v-banner v-if="deferredPrompt" color="info" dark class="text-left">
        <template v-slot:actions>
          <v-btn text @click="dismiss">Dismiss</v-btn>

          <v-btn text @click="install">Install</v-btn>
        </template>
      </v-banner>

      <router-view> </router-view>
    </v-app>
  </div>
</template>
<style scoped>
.about {
  display: flex;
  height: 100vh;
  width: 100%;
  align-items: center;
  justify-content: center;
}
</style>
