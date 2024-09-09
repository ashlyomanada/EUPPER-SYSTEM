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
    path: "/resetPassword",
    name: "ResetPassword",
    component: ResetPass,
  },
  {
    path: "/adminHome",
    component: () => import("../views/AdminDashboard.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/home",
    component: () => import("../views/HomeView.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/otp",
    component: () => import("../views/Otp.vue"),
  },
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
});

// Add route guard
router.beforeEach((to, from, next) => {
  if (to.matched.some((record) => record.meta.requiresAuth)) {
    if (!sessionStorage.getItem("id")) {
      next("/");
    } else {
      next();
    }
  } else {
    next();
  }
});

export default router;
