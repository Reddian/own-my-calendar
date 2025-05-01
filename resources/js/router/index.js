import { createRouter, createWebHistory } from 'vue-router';
import { useStore } from 'vuex'; // Import useStore to check auth state

// Layouts
import AppLayout from '../layouts/AppLayout.vue';

// Views (dynamically imported)
const Home = () => import("../views/Home.vue");
const Calendar = () => import("../views/Calendar.vue");
const History = () => import("../views/History.vue");
const Settings = () => import("../views/Settings.vue");
const Extension = () => import("../views/Extension.vue");
const Terms = () => import("../views/Terms.vue");
const Privacy = () => import("../views/Privacy.vue");

// Auth Views (dynamically imported)
const Login = () => import("../views/Login.vue");
const Register = () => import("../views/Register.vue");
const ForgotPassword = () => import("../views/ForgotPassword.vue");
const ResetPassword = () => import("../views/ResetPassword.vue");
const VerifyEmail = () => import("../views/VerifyEmail.vue");

// Other Views
// const Subscription = () => import("../views/Subscription.vue");
// const NotFound = () => import('../views/NotFound.vue');

const routes = [
  {
    path: '/',
    component: AppLayout,
    meta: { requiresAuth: true }, // Main app requires authentication
    children: [
      {
        path: '', // Default child route for '/'
        name: 'home-root',
        component: Home,
      },
      {
        path: 'home',
        name: 'home',
        component: Home,
      },
      {
        path: 'calendar',
        name: 'calendar',
        component: Calendar,
      },
      {
        path: 'history',
        name: 'history',
        component: History,
      },
      {
        path: 'settings',
        name: 'settings',
        component: Settings,
      },
      {
        path: 'extension',
        name: 'extension',
        component: Extension,
      },
      {
        path: 'terms',
        name: 'terms',
        component: Terms,
      },
      {
        path: 'privacy',
        name: 'privacy',
        component: Privacy,
      },
      // {
      //   path: 'subscription',
      //   name: 'subscription',
      //   component: Subscription,
      // },
    ]
  },
  // Authentication Routes (outside the main layout)
  {
    path: "/login",
    name: "login",
    component: Login,
    meta: { requiresGuest: true } // Only accessible to guests
  },
  {
    path: "/register",
    name: "register",
    component: Register,
    meta: { requiresGuest: true } // Only accessible to guests
  },
  {
    path: "/forgot-password",
    name: "forgot-password",
    component: ForgotPassword,
    meta: { requiresGuest: true } // Only accessible to guests
  },
  {
    path: "/password/reset/:token", // Path includes token parameter
    name: "password.reset", // Match Laravel's typical naming
    component: ResetPassword,
    meta: { requiresGuest: true } // Only accessible to guests
  },
  {
    path: "/email/verify",
    name: "verification.notice", // Match Laravel's typical naming
    component: VerifyEmail,
    meta: { requiresAuth: true } // Requires user to be logged in (but maybe not verified)
  },

  // Catch-all route for 404 Not Found (Optional)
  // { 
  //   path: '/:pathMatch(.*)*', 
  //   name: 'NotFound', 
  //   component: NotFound 
  // }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  linkActiveClass: 'active',
  linkExactActiveClass: 'exact-active',
});

// Navigation Guards
router.beforeEach(async (to, from, next) => {
  const store = useStore(); // Get store instance inside guard
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
  const requiresGuest = to.matched.some(record => record.meta.requiresGuest);

  // Check authentication status only once if needed
  // We assume the user state is already loaded or will be checked
  // A simple check might be looking for a user object in the store
  const isAuthenticated = !!store.state.user.user; // Adjust based on your Vuex state structure
  const isVerified = store.state.user.user ? store.state.user.user.email_verified_at : false; // Check if verified

  if (requiresAuth && !isAuthenticated) {
    // If route requires auth and user is not authenticated, redirect to login
    console.log('Guard: Requires auth, not authenticated. Redirecting to login.');
    next({ name: 'login' });
  } else if (requiresGuest && isAuthenticated) {
    // If route requires guest and user is authenticated, redirect to home
    console.log('Guard: Requires guest, authenticated. Redirecting to home.');
    next({ name: 'home' });
  } else if (requiresAuth && isAuthenticated && !isVerified && to.name !== 'verification.notice') {
      // If route requires auth, user is authenticated BUT NOT verified,
      // and they are trying to access something other than the verification notice page,
      // redirect them to the verification notice page.
      console.log('Guard: Requires auth, authenticated but not verified. Redirecting to verification notice.');
      next({ name: 'verification.notice' });
  } else if (to.name === 'verification.notice' && isVerified) {
      // If user is already verified and tries to access verification notice, redirect home
      console.log('Guard: Verified user accessing verification notice. Redirecting to home.');
      next({ name: 'home' });
  } else {
    // Otherwise, allow navigation
    console.log('Guard: Allowing navigation.');
    next();
  }
});

export default router;

