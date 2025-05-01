import 'bootstrap/dist/css/bootstrap.min.css';

// Import Font Awesome CSS
import '@fortawesome/fontawesome-free/css/all.min.css'; // Correct path confirmed

import './bootstrap'; // Sets up window.axios with defaults
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

// Function to initialize and mount the Vue app
async function initializeApp() {
    try {
        // Fetch CSRF cookie from Sanctum before mounting the app
        console.log('Fetching CSRF cookie from /sanctum/csrf-cookie...'); // DEBUG
        await window.axios.get('/sanctum/csrf-cookie');
        console.log('CSRF cookie fetched successfully.'); // DEBUG

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
        console.log('Vue app mounted.'); // DEBUG

    } catch (error) {
        console.error('Failed to fetch CSRF cookie or mount Vue app:', error);
        // Handle error appropriately, maybe show a message to the user
        document.getElementById('app').innerHTML = '<div class="alert alert-danger">Failed to initialize the application. Please try refreshing the page.</div>';
    }
}

// Call the initialization function
initializeApp();

