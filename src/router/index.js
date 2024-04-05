import { createRouter, createWebHistory } from "vue-router";
import Register from "../views/Register.vue";

const routes = [
  {
    path: "/",
    component: () => import("../views/Login.vue"),
  },
  {
    path: "/register",
    component: Register,
  },
  {
    path: "/admin",
    component: () => import("../views/AdminDashboard.vue"),
  },
  {
    path: "/home",
    component: () => import("../views/HomeView.vue"),
  },
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
});

export default router;
