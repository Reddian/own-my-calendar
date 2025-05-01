<template>
  <div class="login-page">
    <!-- Decorative Elements -->
    <div class="coin coin-sm coin-1"></div>
    <div class="coin coin-md coin-2"></div>
    <div class="coin coin-sm coin-3"></div>
    <div class="coin-stack">
        <div class="coin coin-lg"></div>
    </div>
    <!-- Clock with hour markings -->
    <div class="clock">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>
    <div class="gear"></div>

    <div class="login-container">
        <!-- Logo -->
        <div class="logo-container">
            <!-- Assuming image is in public/images -->
            <img src="/images/OwnMyCalendarLogoLight.png" alt="Own My Calendar" class="logo-image">
        </div>

        <form @submit.prevent="handleLogin" class="login-form">
            <!-- Display general error messages -->
            <div v-if="errorMessage" class="alert alert-danger" role="alert">
                {{ errorMessage }}
            </div>

            <div class="form-group">
                <input 
                    id="email" 
                    type="email" 
                    class="form-control" 
                    :class="{ 'is-invalid': errors.email }" 
                    v-model="form.email" 
                    placeholder="Email" 
                    required 
                    autocomplete="email" 
                    autofocus
                >
                <span v-if="errors.email" class="invalid-feedback" role="alert">
                    <strong>{{ errors.email[0] }}</strong>
                </span>
            </div>

            <div class="form-group">
                <input 
                    id="password" 
                    type="password" 
                    class="form-control" 
                    :class="{ 'is-invalid': errors.password }" 
                    v-model="form.password" 
                    placeholder="Password" 
                    required 
                    autocomplete="current-password"
                >
                <span v-if="errors.password" class="invalid-feedback" role="alert">
                    <strong>{{ errors.password[0] }}</strong>
                </span>
            </div>

            <div class="form-group">
                <button type="submit" class="btn-login" :disabled="loading">
                    {{ loading ? 'Logging in...' : 'Log in' }}
                </button>
            </div>

            <div class="form-group remember-me-container">
                <div class="form-check">
                    <input 
                        class="form-check-input" 
                        type="checkbox" 
                        v-model="form.remember" 
                        id="remember"
                    >
                    <label class="form-check-label" for="remember">
                        Remember Me
                    </label>
                </div>
            </div>

            <div class="auth-links">
                <!-- Assuming password reset and register are still standard Laravel routes -->
                <div class="forgot-password">
                    <a href="/password/reset">
                        <i class="fas fa-key me-1"></i>Forgot Your Password?
                    </a>
                </div>
                <div class="register-link">
                    <span>Don't have an account?</span>
                    <a href="/register">
                        Create one
                    </a>
                </div>
            </div>
        </form>

        <div class="tagline">More money, more time</div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'; // Add onMounted
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useStore } from 'vuex';

// Add log message on component mount
onMounted(() => {
  console.log("Login.vue component mounted successfully.");
});

const router = useRouter();
const store = useStore();

const form = reactive({
  email: '',
  password: '',
  remember: false,
});

const loading = ref(false);
const errorMessage = ref(''); // For general errors
const errors = ref({}); // For specific field errors

async function handleLogin() {
  loading.value = true;
  errorMessage.value = '';
  errors.value = {};

  try {
    // Ensure CSRF cookie is set (already handled in app.js)
    // await axios.get('/sanctum/csrf-cookie'); 
    
    console.log('Attempting login with:', form.email); // DEBUG
    // Make the login request to a new API endpoint (to be created)
    await axios.post('/api/login', form); // Changed from /login to /api/login
    console.log('Login request successful'); // DEBUG

    // Fetch user data after successful login
    // await store.dispatch('user/fetchUser'); // Assuming a Vuex action exists
    console.log('Login successful, fetching user data...'); // DEBUG
    // TODO: Implement fetchUser action in Vuex store

    // Redirect to the dashboard or intended page
    console.log('Redirecting to /home...'); // DEBUG
    router.push('/home'); 

  } catch (error) {
    console.error('Login failed:', error); // DEBUG
    loading.value = false;
    if (error.response) {
      if (error.response.status === 422) {
        // Validation errors
        errors.value = error.response.data.errors;
        errorMessage.value = error.response.data.message || 'Validation failed.';
      } else if (error.response.status === 419) {
        // CSRF token mismatch - should be less likely now
        errorMessage.value = 'Your session expired. Please refresh the page and try again.';
      } else {
        // Other errors (e.g., invalid credentials)
        errorMessage.value = error.response.data.message || 'Login failed. Please check your credentials.';
      }
    } else {
      errorMessage.value = 'An unexpected error occurred. Please try again.';
    }
  }
}
</script>

<style scoped>
/* Import or define styles similar to auth.css or the original blade template */
/* Assuming global styles for .login-page, .coin, .clock, .gear etc. are loaded */

.login-container {
  /* Styles from auth.css or similar */
  max-width: 400px;
  margin: 5rem auto;
  padding: 2rem;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  position: relative; /* Ensure it's above decorative elements if needed */
  z-index: 10;
}

.logo-container {
  text-align: center;
  margin-bottom: 1.5rem;
}

.logo-image {
  max-width: 200px;
  height: auto;
}

.login-form .form-group {
  margin-bottom: 1rem;
}

.btn-login {
  width: 100%;
  padding: 0.75rem;
  background-color: #6a1b9a; /* Example primary color */
  color: #fff;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.btn-login:hover {
  background-color: #4a148c; /* Darker shade */
}

.btn-login:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.remember-me-container {
  display: flex;
  justify-content: center; /* Center the checkbox */
  margin-bottom: 1rem;
}

.auth-links {
  text-align: center;
  margin-top: 1.5rem;
  font-size: 0.9rem;
}

.auth-links div {
  margin-bottom: 0.5rem;
}

.auth-links a {
  color: #6a1b9a; /* Example primary color */
  text-decoration: none;
}

.auth-links a:hover {
  text-decoration: underline;
}

.register-link span {
  margin-right: 0.5rem;
}

.tagline {
  text-align: center;
  margin-top: 2rem;
  color: #666;
  font-style: italic;
}

/* Add styles for decorative elements if not globally available */
.login-page {
    position: relative;
    min-height: 100vh;
    background-color: #f8f9fa; /* Light background */
    overflow: hidden; /* Hide overflowing decorative elements */
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Basic styles for decorative elements - adjust positions as needed */
.coin {
    position: absolute;
    background-color: #ffd700; /* Gold color */
    border-radius: 50%;
    box-shadow: 2px 2px 5px rgba(0,0,0,0.2);
}
.coin-sm { width: 20px; height: 20px; }
.coin-md { width: 40px; height: 40px; }
.coin-lg { width: 60px; height: 60px; }

.coin-1 { top: 10%; left: 15%; }
.coin-2 { top: 25%; right: 10%; }
.coin-3 { bottom: 15%; left: 20%; }

.coin-stack {
    position: absolute;
    bottom: 5%;
    right: 15%;
}

.clock {
    position: absolute;
    top: 15%;
    right: 20%;
    width: 80px;
    height: 80px;
    border: 3px solid #ccc;
    border-radius: 50%;
}
.clock span {
    position: absolute;
    width: 2px;
    height: 5px;
    background-color: #ccc;
    top: 50%;
    left: 50%;
    transform-origin: 50% 0;
}
/* Position clock markings (example for 12, 3, 6, 9) */
.clock span:nth-child(1) { transform: translate(-50%, -50%) rotate(0deg) translateY(-35px); }
.clock span:nth-child(4) { transform: translate(-50%, -50%) rotate(90deg) translateY(-35px); }
.clock span:nth-child(7) { transform: translate(-50%, -50%) rotate(180deg) translateY(-35px); }
.clock span:nth-child(10) { transform: translate(-50%, -50%) rotate(270deg) translateY(-35px); }
/* Add more spans and rotations for other hours */

.gear {
    position: absolute;
    bottom: 20%;
    left: 10%;
    width: 50px;
    height: 50px;
    /* Basic gear shape using borders or SVG/image */
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path fill="%23ccc" d="M99.4 58.8c-1.4-2.4-3.7-4.1-6.4-4.9l-7.6-2.2c-.8-.2-1.6-.5-2.4-.8-.8-.3-1.6-.7-2.3-1.1-1.8-1-3.4-2.3-4.8-3.8-.6-.6-1.1-1.3-1.6-2-.5-.7-1-1.4-1.4-2.2-.4-.8-.8-1.6-1.1-2.4-.3-.8-.5-1.6-.8-2.4l-2.2-7.6c-.8-2.7-2.5-5-4.9-6.4-2.4-1.4-5.2-1.9-8-1.5l-8.1 1.2c-.8.1-1.7.2-2.5.2-.8 0-1.7-.1-2.5-.2l-8.1-1.2c-2.8-.4-5.6.1-8 1.5-2.4 1.4-4.1 3.7-4.9 6.4l-2.2 7.6c-.2.8-.5 1.6-.8 2.4-.3.8-.7 1.6-1.1 2.4-.6.8-1.2 1.5-1.8 2.2-.7.7-1.4 1.4-2.2 2-.8.6-1.6 1.1-2.4 1.6-1.8 1.2-3.4 2.7-4.8 4.3-.6.6-1.1 1.3-1.6 2-.5.7-1 1.4-1.4 2.2-.4.8-.8 1.6-1.1 2.4-.3.8-.5 1.6-.8 2.4l-2.2 7.6c-.8 2.7-1 5.5.1 8.1.8 2.6 2.5 4.8 4.8 6.3 2.3 1.5 5 2.1 7.7 1.7l8.1-1.2c.8-.1 1.7-.2 2.5-.2.8 0 1.7.1 2.5.2l8.1 1.2c2.7.4 5.4-.1 7.7-1.7 2.3-1.5 4-3.7 4.8-6.3.8-2.6.6-5.4-.1-8.1l-2.2-7.6c-.2-.8-.5-1.6-.8-2.4-.3-.8-.7-1.6-1.1-2.4-.6-.8-1.2-1.5-1.8-2.2-.7-.7-1.4-1.4-2.2-2-.8-.6-1.6-1.1-2.4-1.6-1.8-1.2-3.4-2.7-4.8-4.3-.6-.6-1.1-1.3-1.6-2-.5-.7-1-1.4-1.4-2.2-.4-.8-.8-1.6-1.1-2.4-.3-.8-.5-1.6-.8-2.4l-2.2-7.6c-.8-2.7-2.5-5-4.9-6.4-2.4-1.4-5.2-1.9-8-1.5l-8.1 1.2c-.8.1-1.7.2-2.5.2-.8 0-1.7-.1-2.5-.2l-8.1-1.2c-2.8-.4-5.6.1-8 1.5-2.4 1.4-4.1 3.7-4.9 6.4l-2.2 7.6c-.2.8-.5 1.6-.8 2.4-.3.8-.7 1.6-1.1 2.4-.6.8-1.2 1.5-1.8 2.2-.7.7-1.4 1.4-2.2 2-.8.6-1.6 1.1-2.4 1.6-1.8 1.2-3.4 2.7-4.8 4.3-.6.6-1.1 1.3-1.6 2-.5.7-1 1.4-1.4 2.2-.4.8-.8 1.6-1.1 2.4-.3.8-.5 1.6-.8 2.4l-2.2 7.6c-.8 2.7-1 5.5.1 8.1.8 2.6 2.5 4.8 4.8 6.3 2.3 1.5 5 2.1 7.7 1.7l8.1-1.2c.8-.1 1.7-.2 2.5-.2.8 0 1.7.1 2.5.2l8.1 1.2c2.7.4 5.4-.1 7.7-1.7 2.3-1.5 4-3.7 4.8-6.3.8-2.6.6-5.4-.1-8.1l-2.2-7.6c-.2-.8-.5-1.6-.8-2.4-.3-.8-.7-1.6-1.1-2.4-.6-.8-1.2-1.5-1.8-2.2-.7-.7-1.4-1.4-2.2-2-.8-.6-1.6-1.1-2.4-1.6-1.8-1.2-3.4-2.7-4.8-4.3-.6-.6-1.1-1.3-1.6-2-.5-.7-1-1.4-1.4-2.2-.4-.8-.8-1.6-1.1-2.4-.3-.8-.5-1.6-.8-2.4l-2.2-7.6c-.8-2.7-2.5-5-4.9-6.4-2.4-1.4-5.2-1.9-8-1.5l-8.1 1.2c-.8.1-1.7.2-2.5.2-.8 0-1.7-.1-2.5-.2l-8.1-1.2c-2.8-.4-5.6.1-8 1.5-2.4 1.4-4.1 3.7-4.9 6.4l-2.2 7.6c-.2.8-.5 1.6-.8 2.4-.3.8-.7 1.6-1.1 2.4-.6.8-1.2 1.5-1.8 2.2-.7.7-1.4 1.4-2.2 2-.8.6-1.6 1.1-2.4 1.6-1.8 1.2-3.4 2.7-4.8 4.3-.6.6-1.1 1.3-1.6 2-.5.7-1 1.4-1.4 2.2-.4.8-.8 1.6-1.1 2.4-.3.8-.5 1.6-.8 2.4l-2.2 7.6c-.8 2.7-1 5.5.1 8.1.8 2.6 2.5 4.8 4.8 6.3 2.3 1.5 5 2.1 7.7 1.7l8.1-1.2c.8-.1 1.7-.2 2.5-.2.8 0 1.7.1 2.5.2l8.1 1.2c2.7.4 5.4-.1 7.7-1.7 2.3-1.5 4-3.7 4.8-6.3.8-2.6.6-5.4-.1-8.1zM50 35c-8.3 0-15 6.7-15 15s6.7 15 15 15 15-6.7 15-15-6.7-15-15-15z"/></svg>');
    background-size: contain;
    background-repeat: no-repeat;
}

</style>

