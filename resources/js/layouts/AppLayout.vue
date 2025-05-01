<template>
  <div id="app">
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

    <!-- Main content -->
    <div class="dashboard-container">
      <!-- Sidebar -->
      <div class="sidebar" :class="{ active: isMenuActive }">
        <div class="logo-container">
          <img src="/upload/OwnMyCalendarLogoLight.png" alt="Own My Calendar">
        </div>

        <!-- Navigation Links - Will be updated later with vue-router -->
        <div class="nav-item" :class="{ active: $route.path === '/home' || $route.path === '/' }">
          <i class="fas fa-home"></i>
          <span><router-link to="/home">Dashboard</router-link></span>
        </div>

        <div class="nav-item" :class="{ active: $route.path.startsWith('/calendar') }">
          <i class="fas fa-calendar-alt"></i>
          <span><router-link to="/calendar">Calendar</router-link></span>
        </div>

        <div class="nav-item" :class="{ active: $route.path.startsWith('/history') }">
          <i class="fas fa-history"></i>
          <span><router-link to="/history">History</router-link></span>
        </div>

        <div class="nav-item" :class="{ active: $route.path.startsWith('/extension') }">
          <i class="fas fa-puzzle-piece"></i>
          <span><router-link to="/extension">Chrome Extension</router-link></span>
        </div>

        <div class="nav-item" :class="{ active: $route.path.startsWith('/settings') }">
          <i class="fas fa-cog"></i>
          <span><router-link to="/settings">Settings</router-link></span>
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
        <!-- Subscription CTA - Logic needs to be added -->
        <!-- <div class="subscription-cta">
          <div class="cta-content">
            <h4>Upgrade to Premium</h4>
            <p>You have used X of 3 free grades. Get unlimited grades for just $9/month.</p>
          </div>
          <router-link to="/subscription" class="btn btn-primary">Upgrade Now</router-link>
        </div> -->

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
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router'; // Assuming vue-router is used

const isMenuActive = ref(false);
const router = useRouter();

function openMenu() {
  isMenuActive.value = true;
  // Optionally hide the toggle button if needed
  // document.querySelector('.mobile-menu-toggle')?.classList.add('hidden');
}

function closeMenu() {
  isMenuActive.value = false;
  // Optionally show the toggle button if needed
  // document.querySelector('.mobile-menu-toggle')?.classList.remove('hidden');
}

function toggleMenu() {
  if (isMenuActive.value) {
    closeMenu();
  } else {
    openMenu();
  }
}

async function logout() {
  console.log("Logout action triggered");
  try {
    // Make a POST request to Laravel's logout route
    await axios.post("/logout");
    // On successful logout, redirect to the login page
    window.location.href = "/login"; // Use window.location for a full page reload to clear SPA state
  } catch (error) {
    console.error("Logout failed:", error);
    // Optionally show an error message to the user
    alert("Logout failed. Please try again.");
  }
}

// Close menu when route changes
router.afterEach(() => {
  closeMenu();
});

// Add/remove event listeners if needed, e.g., for window resize
onMounted(() => {
  // Add any necessary listeners
});
onUnmounted(() => {
  // Remove listeners to prevent memory leaks
});

</script>

<style>
/* Global styles like fonts.css and dashboard.css are loaded via spa.blade.php */

/* Add any component-specific styles here */
.nav-item a {
    color: inherit; /* Ensure router-links inherit text color */
    text-decoration: none;
}

.nav-item.active span a,
.nav-item:hover span a {
    color: var(--primary-purple); /* Or your active/hover color */
}

/* Ensure router-link clicks work */
.nav-item span {
    cursor: pointer;
}
</style>