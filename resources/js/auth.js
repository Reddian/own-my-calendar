// JS specific to authentication pages

// Import Bootstrap JS (and Popper)
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// Import Axios and configure CSRF token (Needed if auth pages make AJAX requests, unlikely for standard login/register)
// If auth pages ONLY use standard form POSTs, this might not be strictly necessary,
// but it's good practice to have consistent Axios setup if any JS interaction occurs.
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Attempt to read CSRF token from meta tag
let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found for auth pages. Ensure meta tag exists.');
}

console.log('Auth JS loaded with Bootstrap and Axios CSRF setup.');

