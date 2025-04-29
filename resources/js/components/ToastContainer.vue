<template>
    <div class="toast-container">
        <Toast
            v-for="toast in toasts"
            :key="toast.id"
            :show="true"
            :type="toast.type"
            :title="toast.title"
            :message="toast.message"
            :duration="toast.duration"
            @close="removeToast(toast.id)"
        />
    </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import Toast from './Toast.vue';

export default {
    name: 'ToastContainer',
    components: {
        Toast
    },
    computed: {
        ...mapState('toast', ['toasts'])
    },
    methods: {
        ...mapActions('toast', ['removeToast'])
    }
};
</script>

<style scoped>
.toast-container {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
</style> 