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
                <!-- Use Laravel named routes defined in web.php -->
                <div class="forgot-password">
                    <a href="/password/reset"> <!-- Assuming /password/reset is correct path -->
                        <i class="fas fa-key me-1"></i>Forgot Your Password?
                    </a>
                </div>
                <div class="register-link">
                    <span>Don't have an account?</span>
                    <a href="/register"> <!-- Assuming /register is correct path -->
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
/* Combined and adapted styles from modern-login.css and auth-custom.css */

/* Gradient Background & Page Layout */
.login-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #4a6fdc 0%, #6fb1e3 100%); /* From auth-custom */
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 20px;
  position: relative;
  overflow: hidden;
  margin: 0; /* From auth-custom */
}

/* Main Content Container */
.login-container {
  width: 100%;
  max-width: 450px; /* Adjusted from auth-custom */
  margin: 0 auto;
  text-align: center;
  position: relative; /* Added from modern-login */
  z-index: 10;
  padding: 0; /* From auth-custom */
}

/* Logo */
.logo-container {
  text-align: center;
  margin-bottom: 1.5rem; /* Keep existing margin */
}

.logo-image {
  max-width: 200px; /* Keep existing size */
  height: auto;
}

/* Form Elements */
.login-form {
  width: 100%;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 15px; /* From modern-login & auth-custom */
}

.form-control {
  width: 100%;
  padding: 15px 20px; /* From modern-login */
  font-size: 1rem;
  background-color: rgba(255, 255, 255, 0.2); /* From modern-login */
  border: none;
  border-radius: 10px; /* From modern-login */
  color: white;
  backdrop-filter: blur(5px); /* From modern-login */
  transition: all 0.3s ease;
}

.form-control::placeholder {
  color: rgba(255, 255, 255, 0.7); /* From modern-login */
}

.form-control:focus {
  background-color: rgba(255, 255, 255, 0.3); /* From modern-login */
  outline: none;
  box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3); /* From modern-login */
}

/* Validation Feedback (Ensure Bootstrap styles apply or replicate) */
.invalid-feedback {
  display: block; /* Ensure it shows */
  text-align: left;
  color: #ffcdd2; /* Light red for visibility on dark background */
  margin-top: 0.25rem;
}
.form-control.is-invalid {
  border: 1px solid #ef5350; /* Red border for invalid */
}

.btn-login {
  width: 100%;
  padding: 15px; /* From modern-login */
  background-color: #4a90e2; /* From modern-login */
  color: white;
  border: none;
  border-radius: 10px; /* From modern-login */
  font-size: 1.1rem; /* From modern-login */
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* From modern-login */
}

.btn-login:hover {
  background-color: #3a80d2; /* From modern-login */
  transform: translateY(-2px); /* From modern-login */
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3); /* From modern-login */
}

.btn-login:active {
  transform: translateY(0); /* From modern-login */
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* From modern-login */
}

.btn-login:disabled {
  background-color: #ccc;
  cursor: not-allowed;
  opacity: 0.6;
}

/* Remember Me */
.remember-me-container {
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 10px 0 15px 0; /* Adjusted margin */
}

.form-check {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}

.form-check-input {
  margin-right: 8px;
  width: 1.1em; /* Slightly larger checkbox */
  height: 1.1em;
}

.form-check-label {
  margin: 0;
  color: white;
  font-weight: 500;
}

/* Auth Links */
.auth-links {
  margin-top: 20px; /* From auth-custom */
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 15px; /* From auth-custom */
}

.forgot-password a {
  color: #ffd54f; /* From auth-custom */
  font-weight: 600;
  text-decoration: none;
  font-size: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.forgot-password a:hover {
  color: white; /* From auth-custom */
  text-decoration: underline;
}

.register-link {
  margin-top: 5px; /* From auth-custom */
  color: white;
}

.register-link span {
  margin-right: 5px;
}

.register-link a {
  color: #ffd54f; /* From auth-custom */
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
}

.register-link a:hover {
  color: white; /* From auth-custom */
  text-decoration: underline;
}

/* Tagline */
.tagline {
  color: white; /* From modern-login */
  font-size: 1.2rem;
  margin-top: 20px;
  opacity: 0.9;
}

/* Decorative Elements (from modern-login.css) */
.coin {
  position: absolute;
  background-color: #ffc107;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.coin::before {
  content: "$";
  color: #e6a800;
  font-weight: bold;
}

.coin-sm {
  width: 40px;
  height: 40px;
  font-size: 1.2rem;
}

.coin-md {
  width: 60px;
  height: 60px;
  font-size: 1.5rem;
}

.coin-lg {
  width: 100px;
  height: 100px;
  font-size: 2.5rem;
}

.coin-stack {
  position: absolute;
  bottom: 20px;
  left: 20px;
  z-index: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.coin-stack::before,
.coin-stack::after {
  content: "";
  width: 80px;
  height: 15px;
  background-color: #ffc107;
  border-radius: 10px;
  margin-bottom: 5px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.coin-1 { top: 100px; right: 100px; }
.coin-2 { top: 40%; left: 10%; }
.coin-3 { bottom: 30%; left: 50%; }

.clock {
  position: absolute;
  bottom: 50px;
  right: 50px;
  width: 100px;
  height: 100px;
  background-color: #5b8def;
  border-radius: 50%;
  border: 5px solid #4a7bce;
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
  z-index: 5;
}

.clock::before { /* Minute hand */
  content: "";
  position: absolute;
  width: 5px;
  height: 30px;
  background-color: white;
  border-radius: 5px;
  transform: rotate(-45deg);
  transform-origin: bottom center;
  bottom: 50%; /* Position origin at center */
  left: calc(50% - 2.5px);
}

.clock::after { /* Hour hand */
  content: "";
  position: absolute;
  width: 5px;
  height: 20px;
  background-color: white;
  border-radius: 5px;
  transform: rotate(45deg);
  transform-origin: bottom center;
  bottom: 50%; /* Position origin at center */
  left: calc(50% - 2.5px);
}

/* Clock markings */
.clock span {
  position: absolute;
  width: 4px;
  height: 10px;
  background-color: white;
  border-radius: 2px;
  top: 50%; /* Center vertically */
  left: 50%; /* Center horizontally */
  transform-origin: 50% 0; /* Rotate around top center */
}

.clock span:nth-child(1) { transform: translate(-50%, -50%) rotate(0deg) translateY(-40px); }
.clock span:nth-child(2) { transform: translate(-50%, -50%) rotate(30deg) translateY(-40px); }
.clock span:nth-child(3) { transform: translate(-50%, -50%) rotate(60deg) translateY(-40px); }
.clock span:nth-child(4) { transform: translate(-50%, -50%) rotate(90deg) translateY(-40px); }
.clock span:nth-child(5) { transform: translate(-50%, -50%) rotate(120deg) translateY(-40px); }
.clock span:nth-child(6) { transform: translate(-50%, -50%) rotate(150deg) translateY(-40px); }
.clock span:nth-child(7) { transform: translate(-50%, -50%) rotate(180deg) translateY(-40px); }
.clock span:nth-child(8) { transform: translate(-50%, -50%) rotate(210deg) translateY(-40px); }
.clock span:nth-child(9) { transform: translate(-50%, -50%) rotate(240deg) translateY(-40px); }
.clock span:nth-child(10) { transform: translate(-50%, -50%) rotate(270deg) translateY(-40px); }
.clock span:nth-child(11) { transform: translate(-50%, -50%) rotate(300deg) translateY(-40px); }
.clock span:nth-child(12) { transform: translate(-50%, -50%) rotate(330deg) translateY(-40px); }

.gear {
  position: absolute;
  width: 80px;
  height: 80px;
  background-color: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  right: 20%;
  top: 40%;
}

.gear::before {
  content: "";
  position: absolute;
  width: 100%;
  height: 100%;
  background-color: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  transform: rotate(45deg);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
  .login-heading {
    font-size: 2.5rem;
  }
  
  .calendar-icon {
    width: 100px;
    height: 100px;
  }
  
  .coin-lg {
    width: 80px;
    height: 80px;
  }
  
  .clock {
    width: 80px;
    height: 80px;
  }
  
  .gear {
    display: none;
  }
}

@media (max-width: 480px) {
  .login-heading {
    font-size: 2rem;
  }
  
  .calendar-icon {
    width: 80px;
    height: 80px;
  }
  
  .coin-sm, .coin-md {
    display: none;
  }
  
  .coin-lg {
    width: 60px;
    height: 60px;
  }
  
  .clock {
    width: 60px;
    height: 60px;
    right: 20px;
    bottom: 20px;
  }
}

/* Ensure no extra spacing from main app layout */
:global(#app) {
    margin: 0;
    padding: 0;
}
:global(main) {
    padding: 0 !important;
    margin: 0 !important;
}

</style>

