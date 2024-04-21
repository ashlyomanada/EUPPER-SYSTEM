import { createRouter, createWebHistory } from "vue-router";
import Register from "../views/ForgotPass.vue";
import ResetPass from "../views/ResetPass.vue";

const routes = [
  {
    path: "/",
    name: "Login",
    component: () => import("../views/Login.vue"),
  },
  {
    path: "/forgot",
    component: Register,
  },
  {
    path: "/resetPassword/:token", // Route with token parameter
    name: "ResetPassword",
    component: ResetPass,
    props: true,
  },
  {
    path: "/adminHome",
    component: () => import("../views/AdminDashboard.vue"),
  },
  {
    path: "/admin",
    component: () => import("../views/AdminLogin.vue"),
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
