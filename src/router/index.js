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
    meta: { requiresAuth: true }, // Add this meta field to indicate this route requires authentication
  },
  {
    path: "/home",
    component: () => import("../views/HomeView.vue"),
    meta: { requiresAuth: true }, // Add this meta field to indicate this route requires authentication
  },
  {
    path: "/sendNotif",
    component: () => import("../views/SendNotif.vue"),
  },
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
});

// Add route guard
router.beforeEach((to, from, next) => {
  // Check if the route requires authentication
  if (to.matched.some((record) => record.meta.requiresAuth)) {
    // Check if there is a token in session storage (indicating the user is logged in)
    if (!sessionStorage.getItem("id")) {
      // If not logged in, redirect to login page
      next("/");
    } else {
      // If logged in, proceed to the requested route
      next();
    }
  } else {
    // If the route does not require authentication, proceed normally
    next();
  }
});

export default router;
