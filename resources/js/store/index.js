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
      console.log('[Vuex Mutation] SET_USER:', user); // DEBUG
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
      console.log('[Vuex Mutation] CLEAR_USER'); // DEBUG
      state.user = null;
      state.isAuthenticated = false;
    },
    // Mutation to update specific user properties (like timezone)
    UPDATE_USER_PROPERTY(state, { key, value }) {
      if (state.user) {
        console.log(`[Vuex Mutation] UPDATE_USER_PROPERTY: Setting ${key} to`, value); // DEBUG
        state.user = { ...state.user, [key]: value };
      }
    },
  },
  actions: {
    // Action to fetch user data (e.g., after login or on app load)
    async fetchUser({ commit }) {
      console.log('[Vuex Action] fetchUser: Starting...'); // DEBUG
      commit('SET_LOADING', true);
      commit('SET_ERROR', null);
      try {
        // Use the standard Sanctum endpoint to get the authenticated user
        console.log('[Vuex Action] fetchUser: Calling /api/user...'); // DEBUG
        const response = await axios.get('/api/user'); 
        const user = response.data;
        console.log('[Vuex Action] fetchUser: Received user data:', user); // DEBUG
        commit('SET_USER', user);
        console.log('[Vuex Action] fetchUser: SET_USER mutation committed.'); // DEBUG
        return user; // Return user data on success
      } catch (error) {
        console.error('[Vuex Action] fetchUser: Error fetching user:', error.response ? error.response.data : error.message); // DEBUG
        commit('SET_ERROR', 'Failed to load user data.');
        commit('CLEAR_USER'); // Clear user data on error
        console.log('[Vuex Action] fetchUser: CLEAR_USER mutation committed due to error.'); // DEBUG
        return null; // Return null on failure
      } finally {
        commit('SET_LOADING', false);
        console.log('[Vuex Action] fetchUser: Finished.'); // DEBUG
      }
    },

    // Login action
    async login({ commit, dispatch }, credentials) {
      console.log('[Vuex Action] login: Starting...'); // DEBUG
      commit('SET_LOADING', true);
      commit('SET_ERROR', null);
      try {
        // Ensure CSRF cookie is set (should be handled by app.js)
        // await axios.get('/sanctum/csrf-cookie'); 
        
        // Make the login request
        console.log('[Vuex Action] login: Calling /api/login...'); // DEBUG
        await axios.post('/api/login', credentials);
        console.log('[Vuex Action] login: /api/login call successful.'); // DEBUG
        
        // If login is successful, fetch the user data
        console.log('[Vuex Action] login: Dispatching fetchUser...'); // DEBUG
        await dispatch('fetchUser'); 
        console.log('[Vuex Action] login: fetchUser completed.'); // DEBUG
        return true; // Indicate success
      } catch (error) {
        console.error('[Vuex Action] login: Error logging in:', error.response ? error.response.data : error.message); // DEBUG
        const errorMessage = error.response?.data?.message || 'Login failed. Please check your credentials.';
        commit('SET_ERROR', errorMessage);
        commit('CLEAR_USER');
        console.log('[Vuex Action] login: CLEAR_USER committed due to error.'); // DEBUG
        // Re-throw the error so the component can handle UI updates (like showing validation errors)
        throw error; 
      } finally {
        commit('SET_LOADING', false);
        console.log('[Vuex Action] login: Finished.'); // DEBUG
      }
    },

    // Logout action
    async logout({ commit }) {
      console.log('[Vuex Action] logout: Starting...'); // DEBUG
      commit('SET_LOADING', true);
      commit('SET_ERROR', null);
      try {
        // Use the API logout route
        console.log('[Vuex Action] logout: Calling /api/logout...'); // DEBUG
        await axios.post('/api/logout'); 
        console.log('[Vuex Action] logout: /api/logout call successful.'); // DEBUG
        commit('CLEAR_USER');
        console.log('[Vuex Action] logout: CLEAR_USER committed.'); // DEBUG
        // Redirect happens in the component after dispatching this action
      } catch (error) {
        console.error('[Vuex Action] logout: Error logging out:', error.response ? error.response.data : error.message); // DEBUG
        commit('SET_ERROR', 'Logout failed.');
        // Even if logout API fails, clear user state on frontend
        commit('CLEAR_USER'); 
        console.log('[Vuex Action] logout: CLEAR_USER committed due to error.'); // DEBUG
      } finally {
        commit('SET_LOADING', false);
        console.log('[Vuex Action] logout: Finished.'); // DEBUG
      }
    },

    // Action to update user profile (name, email, timezone)
    async updateProfile({ commit, state }, profileData) {
      console.log('[Vuex Action] updateProfile: Starting with data:', profileData); // DEBUG
      commit('SET_LOADING', true);
      commit('SET_ERROR', null);
      try {
        console.log('[Vuex Action] updateProfile: Calling PUT /api/profile...'); // DEBUG
        const response = await axios.put('/api/profile', profileData);
        const updatedUser = response.data.user; // Assuming backend returns updated user object
        console.log('[Vuex Action] updateProfile: Received updated user data:', updatedUser); // DEBUG
        
        // Update the user state with the new data
        commit('SET_USER', updatedUser);
        console.log('[Vuex Action] updateProfile: SET_USER mutation committed.'); // DEBUG
        return updatedUser; // Return updated user data on success
      } catch (error) {
        console.error('[Vuex Action] updateProfile: Error updating profile:', error.response ? error.response.data : error.message); // DEBUG
        const errorMessage = error.response?.data?.message || 'Failed to update profile.';
        commit('SET_ERROR', errorMessage);
        // Re-throw the error so the component can display it
        throw new Error(errorMessage);
      } finally {
        commit('SET_LOADING', false);
        console.log('[Vuex Action] updateProfile: Finished.'); // DEBUG
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

