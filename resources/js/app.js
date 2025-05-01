import 'bootstrap/dist/css/bootstrap.min.css';

// Import Font Awesome CSS
import '@fortawesome/fontawesome-free/css/all.min.css'; // Correct path confirmed

import './bootstrap'; // If bootstrap.js setup is needed (e.g., for Axios CSRF header)
import { createApp } from 'vue';

// Import the main App component, router, and store
import App from './App.vue';
import router from './router';
import store from './store'; // Import the store

// Import global styles managed by Vite
import '../css/fonts.css'; 
import '../css/dashboard.css';
import '../css/bootstrap-custom.css';
// Note: resources/css/app.css is already included via @vite in spa.blade.php

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

