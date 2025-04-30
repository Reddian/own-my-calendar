import { createStore } from 'vuex';

// Import modules if you create them (e.g., user module)
// import user from './modules/user';

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
    // Example action to fetch user data (e.g., after login or on app load)
    async fetchUser({ commit }) {
      commit('SET_LOADING', true);
      commit('SET_ERROR', null);
      try {
        // Replace with your actual API call to get user data
        // const response = await axios.get('/api/user');
        // const user = response.data;
        
        // Placeholder user data
        const user = {
          id: 1,
          name: 'Test User',
          email: 'test@example.com',
          isSubscribed: true, // Example property
          // Add other relevant user properties
        };
        
        commit('SET_USER', user);
      } catch (error) {
        console.error('Error fetching user:', error);
        commit('SET_ERROR', 'Failed to load user data.');
        commit('CLEAR_USER'); // Clear user data on error
      } finally {
        commit('SET_LOADING', false);
      }
    },
    // Example logout action
    async logout({ commit }) {
      commit('SET_LOADING', true);
      commit('SET_ERROR', null);
      try {
        // Replace with your actual API call to logout
        // await axios.post('/logout');
        console.log('Simulating logout...');
        commit('CLEAR_USER');
        // Optionally redirect using the router instance if needed
        // router.push('/login'); 
      } catch (error) {
        console.error('Error logging out:', error);
        commit('SET_ERROR', 'Logout failed.');
      } finally {
        commit('SET_LOADING', false);
      }
    },
    // Add other actions like login, register, updateProfile, etc.
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

