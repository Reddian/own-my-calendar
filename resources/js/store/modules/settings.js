import axios from 'axios';

const state = {
  profile: {
    name: '',
    email: ''
  },
  calendar: {
    autoSync: false,
    showWeekends: true
  },
  notifications: {
    email: true,
    weeklySummary: true
  },
  isLoading: false,
  error: null
};

const getters = {
  profile: state => state.profile,
  calendarSettings: state => state.calendar,
  notificationSettings: state => state.notifications,
  isLoading: state => state.isLoading,
  error: state => state.error
};

const mutations = {
  SET_PROFILE(state, profile) {
    state.profile = profile;
  },
  SET_CALENDAR_SETTINGS(state, settings) {
    state.calendar = settings;
  },
  SET_NOTIFICATION_SETTINGS(state, settings) {
    state.notifications = settings;
  },
  SET_LOADING(state, isLoading) {
    state.isLoading = isLoading;
  },
  SET_ERROR(state, error) {
    state.error = error;
  },
  CLEAR_ERROR(state) {
    state.error = null;
  }
};

const actions = {
  async fetchProfile({ commit }) {
    commit('SET_LOADING', true);
    commit('CLEAR_ERROR');
    try {
      const response = await axios.get('/profile/me');
      commit('SET_PROFILE', response.data);
      return true;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Failed to fetch profile');
      return false;
    } finally {
      commit('SET_LOADING', false);
    }
  },

  async updateProfile({ commit }, profile) {
    commit('SET_LOADING', true);
    commit('CLEAR_ERROR');
    try {
      const response = await axios.put('/profile/me', profile);
      commit('SET_PROFILE', response.data);
      return true;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Failed to update profile');
      return false;
    } finally {
      commit('SET_LOADING', false);
    }
  },

  async fetchSettings({ commit }) {
    commit('SET_LOADING', true);
    commit('CLEAR_ERROR');
    try {
      const response = await axios.get('/settings');
      commit('SET_CALENDAR_SETTINGS', response.data.calendar);
      commit('SET_NOTIFICATION_SETTINGS', response.data.notifications);
      return true;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Failed to fetch settings');
      return false;
    } finally {
      commit('SET_LOADING', false);
    }
  },

  async updateCalendarSettings({ commit }, settings) {
    commit('SET_LOADING', true);
    commit('CLEAR_ERROR');
    try {
      const response = await axios.put('/settings/calendar', settings);
      commit('SET_CALENDAR_SETTINGS', response.data);
      return true;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Failed to update calendar settings');
      return false;
    } finally {
      commit('SET_LOADING', false);
    }
  },

  async updateNotificationSettings({ commit }, settings) {
    commit('SET_LOADING', true);
    commit('CLEAR_ERROR');
    try {
      const response = await axios.put('/settings/notifications', settings);
      commit('SET_NOTIFICATION_SETTINGS', response.data);
      return true;
    } catch (error) {
      commit('SET_ERROR', error.response?.data?.message || 'Failed to update notification settings');
      return false;
    } finally {
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