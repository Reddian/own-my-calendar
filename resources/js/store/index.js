import { createStore } from 'vuex';
import axios from 'axios';

export default createStore({
  state: {
    user: null,
    profile: null,
    grades: [],
    currentWeekGrade: null,
    isAuthenticated: false,
    hasCompletedOnboarding: false,
    loading: false,
    error: null
  },
  getters: {
    user: state => state.user,
    profile: state => state.profile,
    grades: state => state.grades,
    currentWeekGrade: state => state.currentWeekGrade,
    isAuthenticated: state => state.isAuthenticated,
    hasCompletedOnboarding: state => state.hasCompletedOnboarding,
    loading: state => state.loading,
    error: state => state.error
  },
  mutations: {
    SET_USER(state, user) {
      state.user = user;
      state.isAuthenticated = !!user;
    },
    SET_PROFILE(state, profile) {
      state.profile = profile;
      state.hasCompletedOnboarding = !!profile;
    },
    SET_GRADES(state, grades) {
      state.grades = grades;
    },
    SET_CURRENT_WEEK_GRADE(state, grade) {
      state.currentWeekGrade = grade;
    },
    SET_LOADING(state, loading) {
      state.loading = loading;
    },
    SET_ERROR(state, error) {
      state.error = error;
    },
    CLEAR_ERROR(state) {
      state.error = null;
    }
  },
  actions: {
    // Authentication actions
    async login({ commit }, credentials) {
      try {
        commit('SET_LOADING', true);
        commit('CLEAR_ERROR');
        const response = await axios.post('/api/login', credentials);
        localStorage.setItem('token', response.data.token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
        commit('SET_USER', response.data.user);
        return response;
      } catch (error) {
        commit('SET_ERROR', error.response?.data?.message || 'Login failed');
        throw error;
      } finally {
        commit('SET_LOADING', false);
      }
    },
    async register({ commit }, userData) {
      try {
        commit('SET_LOADING', true);
        commit('CLEAR_ERROR');
        const response = await axios.post('/api/register', userData);
        localStorage.setItem('token', response.data.token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
        commit('SET_USER', response.data.user);
        return response;
      } catch (error) {
        commit('SET_ERROR', error.response?.data?.message || 'Registration failed');
        throw error;
      } finally {
        commit('SET_LOADING', false);
      }
    },
    async logout({ commit }) {
      try {
        commit('SET_LOADING', true);
        await axios.post('/api/logout');
        localStorage.removeItem('token');
        delete axios.defaults.headers.common['Authorization'];
        commit('SET_USER', null);
        commit('SET_PROFILE', null);
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        commit('SET_LOADING', false);
      }
    },
    async fetchUser({ commit }) {
      try {
        commit('SET_LOADING', true);
        const token = localStorage.getItem('token');
        if (token) {
          axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
          const response = await axios.get('/api/user');
          commit('SET_USER', response.data);
        }
      } catch (error) {
        console.error('Fetch user error:', error);
        localStorage.removeItem('token');
        delete axios.defaults.headers.common['Authorization'];
        commit('SET_USER', null);
      } finally {
        commit('SET_LOADING', false);
      }
    },
    
    // Profile actions
    async fetchProfile({ commit }) {
      try {
        commit('SET_LOADING', true);
        const response = await axios.get('/api/profile');
        commit('SET_PROFILE', response.data.profile);
        return response.data;
      } catch (error) {
        console.error('Fetch profile error:', error);
        if (error.response?.status === 404) {
          commit('SET_PROFILE', null);
        }
      } finally {
        commit('SET_LOADING', false);
      }
    },
    async saveProfile({ commit }, profileData) {
      try {
        commit('SET_LOADING', true);
        commit('CLEAR_ERROR');
        const response = await axios.post('/api/profile', profileData);
        commit('SET_PROFILE', response.data.profile);
        return response.data;
      } catch (error) {
        commit('SET_ERROR', error.response?.data?.message || 'Failed to save profile');
        throw error;
      } finally {
        commit('SET_LOADING', false);
      }
    },
    
    // Onboarding actions
    async completeOnboarding({ commit }, onboardingData) {
      try {
        commit('SET_LOADING', true);
        commit('CLEAR_ERROR');
        const response = await axios.post('/api/onboarding', onboardingData);
        commit('SET_PROFILE', response.data.profile);
        return response.data;
      } catch (error) {
        commit('SET_ERROR', error.response?.data?.message || 'Failed to complete onboarding');
        throw error;
      } finally {
        commit('SET_LOADING', false);
      }
    },
    
    // Calendar grade actions
    async fetchGrades({ commit }) {
      try {
        commit('SET_LOADING', true);
        const response = await axios.get('/api/grades');
        commit('SET_GRADES', response.data.grades);
        return response.data;
      } catch (error) {
        console.error('Fetch grades error:', error);
      } finally {
        commit('SET_LOADING', false);
      }
    },
    async fetchCurrentWeekGrade({ commit }) {
      try {
        commit('SET_LOADING', true);
        const response = await axios.get('/api/grades/current-week');
        commit('SET_CURRENT_WEEK_GRADE', response.data.grade);
        return response.data;
      } catch (error) {
        console.error('Fetch current week grade error:', error);
      } finally {
        commit('SET_LOADING', false);
      }
    },
    async saveGrade({ commit, dispatch }, gradeData) {
      try {
        commit('SET_LOADING', true);
        commit('CLEAR_ERROR');
        const response = await axios.post('/api/grades', gradeData);
        dispatch('fetchGrades');
        dispatch('fetchCurrentWeekGrade');
        return response.data;
      } catch (error) {
        commit('SET_ERROR', error.response?.data?.message || 'Failed to save grade');
        throw error;
      } finally {
        commit('SET_LOADING', false);
      }
    }
  }
});
