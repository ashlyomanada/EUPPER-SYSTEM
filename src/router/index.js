import { createRouter, createWebHistory } from "vue-router";
import Register from "../views/Register.vue";

const routes = [
  {
    path: "/",
    component: Register,
  },
  {
    path: "/register",
    component: () => import("../views/Rehistro.vue"),
  },
  {
    path: "/manage",
    component: () => import("../views/IndexPage.vue"),
  },
  {
    path: "/admin",
    component: () => import("../views/AdminDashboard.vue"),
  },
  {
    path: "/home",
    component: () => import("../views/HomeView.vue"),
  },
  {
    path: "/login",
    component: () => import("../views/Login.vue"),
  },
  {
    path: "/AdminLogin",
    component: () => import("../views/AdminLogin.vue"),
  },
  {
    path: "/page",
    component: () => import("../views/IndexPage.vue"),
  },
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
});

export default router;
