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
        // Create the Vue application instance first
        const app = createApp(App);

        // Use the router and store
        app.use(router);
        app.use(store);

        // Mount the application immediately to prevent blank screen
        app.mount('#app');
        console.log('[App Init] Vue app mounted.'); // DEBUG

        // Fetch CSRF cookie from Sanctum after mounting
        console.log('[App Init] Fetching CSRF cookie from /sanctum/csrf-cookie...'); // DEBUG
        await window.axios.get('/sanctum/csrf-cookie');
        console.log('[App Init] CSRF cookie fetched successfully.'); // DEBUG

        // Attempt to fetch initial user data when the app loads
        // This helps establish the auth state before initial navigation guards run
        console.log('[App Init] Dispatching initial user/fetchUser...'); // DEBUG
        try {
            await store.dispatch('user/fetchUser');
            console.log('[App Init] Initial user/fetchUser completed successfully.'); // DEBUG
        } catch (userError) {
            // If fetching user fails, don't fail the whole app initialization
            // This allows the app to start in a logged-out state
            console.warn('[App Init] Failed to fetch user, continuing as logged out:', userError);
            // Ensure user state is cleared to prevent stale auth state
            store.commit('user/CLEAR_USER');
            // Redirect to login if not already there
            if (router.currentRoute.value.name !== 'login') {
                router.push({ name: 'login' });
            }
        }
    } catch (error) {
        console.error('[App Init] Error during app initialization:', error);
        // Handle error appropriately, maybe show a message to the user
        document.getElementById('app').innerHTML = '<div class="alert alert-danger">Failed to initialize the application. Please try refreshing the page.</div>';
    }
}

// Call the initialization function
initializeApp();

