export default {
    namespaced: true,
    state: {
        toasts: []
    },
    mutations: {
        ADD_TOAST(state, toast) {
            state.toasts.push({
                id: Date.now(),
                ...toast
            });
        },
        REMOVE_TOAST(state, id) {
            state.toasts = state.toasts.filter(toast => toast.id !== id);
        },
        CLEAR_TOASTS(state) {
            state.toasts = [];
        }
    },
    actions: {
        showToast({ commit }, { type, title, message, duration = 5000 }) {
            const toast = {
                type,
                title,
                message,
                duration
            };
            commit('ADD_TOAST', toast);
        },
        success({ dispatch }, { title, message, duration }) {
            dispatch('showToast', { type: 'success', title, message, duration });
        },
        error({ dispatch }, { title, message, duration }) {
            dispatch('showToast', { type: 'error', title, message, duration });
        },
        warning({ dispatch }, { title, message, duration }) {
            dispatch('showToast', { type: 'warning', title, message, duration });
        },
        info({ dispatch }, { title, message, duration }) {
            dispatch('showToast', { type: 'info', title, message, duration });
        },
        removeToast({ commit }, id) {
            commit('REMOVE_TOAST', id);
        },
        clearToasts({ commit }) {
            commit('CLEAR_TOASTS');
        }
    },
    getters: {
        toasts: state => state.toasts
    }
}; 