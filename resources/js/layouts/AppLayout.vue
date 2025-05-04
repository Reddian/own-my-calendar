<template>
  <div id="app">
    <!-- Onboarding Form takes precedence if not completed -->
    <OnboardingForm 
      v-if="isAuthenticated && user && !user.has_completed_onboarding"
      @onboarding-complete="handleOnboardingComplete"
    />

    <!-- Main App Layout (Sidebar, Content, Footer) shown only if onboarding is complete -->
    <div v-else-if="isAuthenticated && user && user.has_completed_onboarding" class="dashboard-container">
      <!-- Mobile menu toggle button -->
      <button class="mobile-menu-toggle" @click="toggleMenu">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="3" y1="12" x2="21" y2="12"></line>
          <line x1="3" y1="6" x2="21" y2="6"></line>
          <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
      </button>

      <!-- Menu overlay for closing when clicking outside -->
      <div class="menu-overlay" :class="{ active: isMenuActive }" @click="closeMenu"></div>

      <!-- Mobile header with logo -->
      <div class="mobile-header">
        <div class="logo-container">
          <img src="/upload/OwnMyCalendarLogoLight.png" alt="Own My Calendar">
        </div>
      </div>

      <!-- Sidebar -->
      <div class="sidebar" :class="{ active: isMenuActive }">
        <div class="logo-container">
          <img src="/upload/OwnMyCalendarLogoLight.png" alt="Own My Calendar">
        </div>

        <!-- Navigation Links -->
        <div class="nav-item" :class="{ active: $route.path === 
'/home' }">
          <router-link to="/home">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
          </router-link>
        </div>
        <div class="nav-item" :class="{ active: $route.path === 
'/calendar' }">
          <router-link to="/calendar">
            <i class="fas fa-calendar-alt"></i>
            <span>Calendar</span>
          </router-link>
        </div>
        <div class="nav-item" :class="{ active: $route.path === 
'/grades' }">
           <router-link to="/grades">
             <i class="fas fa-chart-line"></i>
             <span>Grades</span>
           </router-link>
        </div>
        <div class="nav-item" :class="{ active: $route.path === 
'/extension' }">
           <router-link to="/extension">
             <i class="fas fa-puzzle-piece"></i>
             <span>Extension</span>
           </router-link>
        </div>
        <div class="nav-item" :class="{ active: $route.path === 
'/settings' }">
          <router-link to="/settings">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
          </router-link>
        </div>

        <div class="nav-item mt-auto">
          <i class="fas fa-sign-out-alt"></i>
          <span>
            <a href="#" @click.prevent="logout">Logout</a>
          </span>
        </div>
      </div>

      <!-- Main Content Area -->
      <div class="main-content">
        <!-- Subscription CTA -->
        <div v-if="showPremiumCTA" class="subscription-cta">
          <div class="cta-content">
            <h4>Upgrade to Premium</h4>
            <p>You have used {{ gradesUsed }} of {{ gradesLimit }} free grades. Get unlimited grades for just $9/month.</p>
          </div>
          <router-link to="/settings#subscription" class="btn btn-primary">Upgrade Now</router-link>
        </div>

        <router-view></router-view> <!-- Where nested routes will be rendered -->
      </div>

      <!-- Footer -->
      <footer class="app-footer">
        <div class="footer-content">
          <div class="footer-links">
            <router-link to="/terms">Terms of Service</router-link>
            <router-link to="/privacy">Privacy Policy</router-link>
          </div>
          <div class="footer-copyright">
            &copy; {{ new Date().getFullYear() }} AI Momentum Lab. All rights reserved.
          </div>
        </div>
      </footer>
    </div>

    <!-- Optional: Loading state while checking auth/onboarding status -->
    <div v-else>
      <!-- You could put a loading spinner here -->
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import axios from "axios"; // Ensure axios is imported
import OnboardingForm from "../components/OnboardingForm.vue"; // Import the onboarding form

const store = useStore();
const router = useRouter();
const isMenuActive = ref(false);

// --- Computed properties for user and subscription status ---
const isAuthenticated = computed(() => store.getters["user/isAuthenticated"]);
const user = computed(() => store.getters["user/getUser"]);

// Check subscription status based on local data
const isSubscribed = computed(() => {
  return user.value?.subscription?.stripe_status === "active" || user.value?.subscription?.stripe_status === "trialing";
});

const gradesUsed = computed(() => user.value?.subscription?.grades_used ?? 0);
const gradesLimit = computed(() => user.value?.subscription?.grades_limit ?? 3);

const showPremiumCTA = computed(() => {
  return isAuthenticated.value && !isSubscribed.value && router.currentRoute.value.path !== "/settings"; 
});

// --- Menu Logic ---
function openMenu() {
  isMenuActive.value = true;
}

function closeMenu() {
  isMenuActive.value = false;
}

function toggleMenu() {
  isMenuActive.value = !isMenuActive.value;
}

// --- Logout Logic ---
async function logout() {
  console.log("Logout action triggered");
  try {
    await store.dispatch("user/logout");
    console.log("Logout successful, redirecting...");
    window.location.href = "/login";
  } catch (error) {
    console.error("Logout failed in component:", error);
    alert("Logout failed. Please check the console for details.");
  }
}

// --- Onboarding Complete Handler ---
function handleOnboardingComplete() {
  // The form already dispatches fetchUser, which updates the user state.
  // The v-if in the template will automatically switch the view.
  console.log("Onboarding complete event received in AppLayout.");
  // Optionally, redirect to a specific page after onboarding
  // router.push("/home"); 
}

// --- Lifecycle Hooks ---
router.afterEach(() => {
  closeMenu();
});

onMounted(async () => {
  // Always try to fetch user data on mount to get the latest onboarding status
  console.log("AppLayout mounted: Attempting fetchUser...");
  await store.dispatch("user/fetchUser");
  console.log("AppLayout fetchUser complete. User:", user.value);
});

onUnmounted(() => {
  // Clean up listeners if any were added
});

</script>

<style>
/* Global styles like fonts.css and dashboard.css are loaded via spa.blade.php */

/* Add any component-specific styles here */
.nav-item a {
    color: inherit; /* Ensure router-links inherit text color */
    text-decoration: none;
    display: flex; /* Ensure icon and text are aligned */
    align-items: center;
    gap: 10px; /* Space between icon and text */
    padding: 10px 15px; /* Add padding to the link itself */
}

.nav-item.active span,
.nav-item:hover span {
    color: var(--primary-purple); /* Or your active/hover color */
}

.nav-item.active i,
.nav-item:hover i {
    color: var(--primary-purple); /* Apply color to icon too */
}

/* Ensure router-link clicks work */
.nav-item span {
    cursor: pointer;
}

/* Styles for the Subscription CTA */
.subscription-cta {
    background-color: #e9ecef; /* Light grey background */
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem; /* Space below the CTA */
    border-radius: 0.375rem; /* Rounded corners */
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap; /* Allow wrapping on smaller screens */
}

.cta-content {
    flex-grow: 1;
    margin-right: 1rem; /* Space between text and button */
}

.subscription-cta h4 {
    margin-bottom: 0.25rem;
    font-size: 1.1rem;
    font-weight: 600;
}

.subscription-cta p {
    margin-bottom: 0;
    font-size: 0.9rem;
    color: #495057; /* Darker grey text */
}

.subscription-cta .btn-primary {
    white-space: nowrap; /* Prevent button text wrapping */
}

@media (max-width: 768px) {
    .subscription-cta {
        flex-direction: column;
        align-items: flex-start;
    }
    .cta-content {
        margin-right: 0;
        margin-bottom: 0.75rem; /* Space between text and button on mobile */
    }
}

</style>

