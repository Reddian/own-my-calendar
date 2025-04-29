import axios from 'axios';

const state = {
  user: null,
  token: localStorage.getItem('auth_token') || null,
  isLoading: false,
  error: null
};

const getters = {
  isAuthenticated: state => !!state.token,
  currentUser: state => state.user,
  authError: state => state.error,
  isLoading: state => state.isLoading
};

const mutations = {
  SET_TOKEN(state, token) {
    state.token = token;
    if (token) {
      localStorage.setItem('auth_token', token);
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    } else {
      localStorage.removeItem('auth_token');
      delete axios.defaults.headers.common['Authorization'];
    }
  },
  SET_USER(state, user) {
    state.user = user;
  },
  SET_LOADING(state, isLoading) {
    state.isLoading = isLoading;
  },
  SET_ERROR(state, error) {
    state.error = error;
  },
  CLEAR_ERROR(state) {
    state.error = null;
  },
  LOGOUT(state) {
    state.user = null;
    state.token = null;
    localStorage.removeItem('auth_token');
    delete axios.defaults.headers.common['Authorization'];
  }
};

const actions = {
  async login({ commit }, credentials) {
    commit('SET_LOADING', true);
    commit('CLEAR_ERROR');
    try {
      const response = await axios.post('/login', credentials);
      const { token, user } = response.data;
      commit('SET_TOKEN', token);
      commit('SET_USER', user);
      return true;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Login failed');
      return false;
    } finally {
      commit('SET_LOADING', false);
    }
  },

  async register({ commit }, userData) {
    commit('SET_LOADING', true);
    commit('CLEAR_ERROR');
    try {
      const response = await axios.post('/register', userData);
      const { token, user } = response.data;
      commit('SET_TOKEN', token);
      commit('SET_USER', user);
      return true;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Registration failed');
      return false;
    } finally {
      commit('SET_LOADING', false);
    }
  },

  async fetchUser({ commit }) {
    commit('SET_LOADING', true);
    commit('CLEAR_ERROR');
    try {
      const response = await axios.get('/profile/me');
      commit('SET_USER', response.data);
      return true;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Failed to fetch user data');
      return false;
    } finally {
      commit('SET_LOADING', false);
    }
  },

  async logout({ commit }) {
    commit('SET_LOADING', true);
    try {
      await axios.post('/logout');
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      commit('LOGOUT');
      commit('SET_LOADING', false);
    }
  },

  clearError({ commit }) {
    commit('CLEAR_ERROR');
  }
};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
}; 