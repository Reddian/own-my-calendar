<template>
  <div class="app">
    <LoadingSpinner v-if="isLoading" size="large" color="primary" text="Loading..." />
    <router-view v-else />
    <ToastContainer />
  </div>
</template>

<script>
import { mapState } from 'vuex';
import LoadingSpinner from './components/LoadingSpinner.vue';
import ToastContainer from './components/ToastContainer.vue';

export default {
  name: 'App',
  components: {
    LoadingSpinner,
    ToastContainer
  },
  computed: {
    ...mapState({
      isLoading: state => state.auth.isLoading || state.settings.isLoading
    })
  }
};
</script>

<style>
/* Global styles */
:root {
  --primary-color: #42b983;
  --secondary-color: #2c3e50;
  --danger-color: #e74c3c;
  --text-color: #2c3e50;
  --text-muted: #666;
  --border-color: #e9ecef;
  --background-color: #f8f9fa;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen,
    Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
  color: var(--text-color);
  background-color: var(--background-color);
  line-height: 1.6;
}

a {
  color: var(--primary-color);
  text-decoration: none;
}

button {
  cursor: pointer;
  font-family: inherit;
}

/* Utility classes */
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.text-center {
  text-align: center;
}

.mt-1 { margin-top: 1rem; }
.mt-2 { margin-top: 2rem; }
.mb-1 { margin-bottom: 1rem; }
.mb-2 { margin-bottom: 2rem; }

.app {
  min-height: 100vh;
  background-color: #f8fafc;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.3s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateX(10px);
  opacity: 0;
}
</style> 