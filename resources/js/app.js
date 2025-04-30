import './bootstrap'; // If bootstrap.js setup is needed (e.g., for Axios CSRF header)
import { createApp } from 'vue';

// Import the main App component, router, and store
import App from './App.vue';
import router from './router';
import store from './store'; // Import the store

// Import global styles if necessary (ensure paths are correct)
// import '../css/app.css'; // Example if you have global CSS

// Create the Vue application instance
const app = createApp(App);

// Use the router and store
app.use(router);
app.use(store); // Use the store

// Optionally, dispatch an action to fetch initial user data when the app loads
// store.dispatch('user/fetchUser'); // Assuming 'user' is the namespace

// Mount the application to the element with id="app"
// Ensure your main Blade view (e.g., spa.blade.php) 
// has <div id="app"></div> where the Vue app will be mounted.
app.mount('#app');

