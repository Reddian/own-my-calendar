import { createStore } from 'vuex';
import axios from 'axios'; // Ensure axios is imported

// Placeholder for a simple user module directly in the main store
const userModule = {
  namespaced: true, // Good practice if you plan to add more modules
  state: () => ({
    user: null, // To store user information (e.g., { id, name, email, isSubscribed, ... })
    isAuthenticated: false, // To track authentication status
    loading: false, // To track loading state for user actions
    error: null, // To store any errors related to user actions
  }),
  mutations: {
    SET_USER(state, user) {
      state.user = user;
      state.isAuthenticated = !!user;
    },
    SET_LOADING(state, loading) {
      state.loading = loading;
    },
    SET_ERROR(state, error) {
      state.error = error;
    },
    CLEAR_USER(state) {
      state.user = null;
      state.isAuthenticated = false;
    },
  },
  actions: {
    // Action to fetch user data (e.g., after login or on app load)
    async fetchUser({ commit }) {
      commit('SET_LOADING', true);
      commit('SET_ERROR', null);
      try {
        // Use the standard Sanctum endpoint to get the authenticated user
        const response = await axios.get('/api/user'); 
        const user = response.data;
        commit('SET_USER', user);
      } catch (error) {
        console.error('Error fetching user:', error);
        commit('SET_ERROR', 'Failed to load user data.');
        commit('CLEAR_USER'); // Clear user data on error
      } finally {
        commit('SET_LOADING', false);
      }
    },

    // Login action
    async login({ commit, dispatch }, credentials) {
      commit('SET_LOADING', true);
      commit('SET_ERROR', null);
      try {
        // Ensure CSRF cookie is set (should be handled by app.js)
        // await axios.get('/sanctum/csrf-cookie'); 
        
        // Make the login request
        await axios.post('/api/login', credentials);
        
        // If login is successful, fetch the user data
        await dispatch('fetchUser'); 
        return true; // Indicate success
      } catch (error) {
        console.error('Error logging in:', error);
        const errorMessage = error.response?.data?.message || 'Login failed. Please check your credentials.';
        commit('SET_ERROR', errorMessage);
        commit('CLEAR_USER');
        // Re-throw the error so the component can handle UI updates (like showing validation errors)
        throw error; 
      } finally {
        commit('SET_LOADING', false);
      }
    },

    // Logout action
    async logout({ commit }) {
      commit('SET_LOADING', true);
      commit('SET_ERROR', null);
      try {
        // Use the API logout route
        await axios.post('/api/logout'); 
        commit('CLEAR_USER');
        // Redirect happens in the component after dispatching this action
      } catch (error) {
        console.error('Error logging out:', error);
        commit('SET_ERROR', 'Logout failed.');
        // Even if logout API fails, clear user state on frontend
        commit('CLEAR_USER'); 
      } finally {
        commit('SET_LOADING', false);
      }
    },
  },
  getters: {
    getUser: (state) => state.user,
    isAuthenticated: (state) => state.isAuthenticated,
    isLoading: (state) => state.loading,
    getError: (state) => state.error,
    // Example getter for subscription status
    isSubscribed: (state) => state.user?.isSubscribed || false,
  },
};

const store = createStore({
  modules: {
    user: userModule,
    // Add other modules here if needed (e.g., notifications, settings)
  },
  // You can also have global state/mutations/actions/getters here if needed
});

export default store;

