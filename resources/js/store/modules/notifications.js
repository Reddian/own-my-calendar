const state = {
  notifications: [],
  nextId: 1
};

const getters = {
  allNotifications: state => state.notifications
};

const mutations = {
  ADD_NOTIFICATION(state, notification) {
    state.notifications.push({
      ...notification,
      id: state.nextId++
    });
  },
  REMOVE_NOTIFICATION(state, id) {
    state.notifications = state.notifications.filter(n => n.id !== id);
  },
  CLEAR_NOTIFICATIONS(state) {
    state.notifications = [];
  }
};

const actions = {
  addNotification({ commit }, { message, type = 'success', duration = 3000 }) {
    const notification = {
      message,
      type,
      duration
    };
    commit('ADD_NOTIFICATION', notification);
    
    if (duration > 0) {
      setTimeout(() => {
        commit('REMOVE_NOTIFICATION', notification.id);
      }, duration);
    }
  },
  
  removeNotification({ commit }, id) {
    commit('REMOVE_NOTIFICATION', id);
  },
  
  clearNotifications({ commit }) {
    commit('CLEAR_NOTIFICATIONS');
  }
};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
}; 