import { createRouter, createWebHistory } from 'vue-router';

// Layouts
import AppLayout from '../layouts/AppLayout.vue';

// Views (dynamically imported for lazy loading)
const Home = () => import('../views/Home.vue');
const Calendar = () => import('../views/Calendar.vue');
const History = () => import('../views/History.vue');
const Settings = () => import('../views/Settings.vue');
// Add imports for other views like Extension, Subscription, Terms, Privacy etc. as they are created
// const Extension = () => import('../views/Extension.vue');
// const Subscription = () => import('../views/Subscription.vue');
// const Terms = () => import('../views/Terms.vue');
// const Privacy = () => import('../views/Privacy.vue');

// Placeholder for Auth views if needed later
// const Login = () => import('../views/auth/Login.vue');
// const Register = () => import('../views/auth/Register.vue');

const routes = [
  {
    path: '/',
    component: AppLayout,
    // redirect: '/home', // Redirect root to dashboard
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
      // Add routes for other pages as needed
      // {
      //   path: 'extension',
      //   name: 'extension',
      //   component: Extension,
      // },
      // {
      //   path: 'subscription',
      //   name: 'subscription',
      //   component: Subscription,
      // },
      // {
      //   path: 'terms',
      //   name: 'terms',
      //   component: Terms,
      // },
      // {
      //   path: 'privacy',
      //   name: 'privacy',
      //   component: Privacy,
      // },
    ]
  },
  // Add routes for authentication if needed (outside the main layout)
  // {
  //   path: '/login',
  //   name: 'login',
  //   component: Login,
  // },
  // {
  //   path: '/register',
  //   name: 'register',
  //   component: Register,
  // },

  // Catch-all route for 404 Not Found
  // { 
  //   path: '/:pathMatch(.*)*', 
  //   name: 'NotFound', 
  //   component: () => import('../views/NotFound.vue') // Create a NotFound component
  // }
];

const router = createRouter({
  history: createWebHistory(), // Using HTML5 history mode
  routes,
  linkActiveClass: 'active', // Optional: class for active links
  linkExactActiveClass: 'exact-active', // Optional: class for exact active links
});

// Add navigation guards if needed (e.g., for authentication)
// router.beforeEach((to, from, next) => {
//   const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
//   const isAuthenticated = // Check authentication status (e.g., from store)
// 
//   if (requiresAuth && !isAuthenticated) {
//     next('/login');
//   } else {
//     next();
//   }
// });

export default router;

