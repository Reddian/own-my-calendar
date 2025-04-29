<template>
  <transition name="fade">
    <div v-if="show" :class="['notification', type]" @click="close">
      <div class="notification-content">
        <span class="notification-icon">
          <i v-if="type === 'success'" class="fas fa-check-circle"></i>
          <i v-else-if="type === 'error'" class="fas fa-exclamation-circle"></i>
        </span>
        <span class="notification-message">{{ message }}</span>
      </div>
    </div>
  </transition>
</template>

<script>
import { ref, watch } from 'vue';

export default {
  name: 'Notification',
  props: {
    message: {
      type: String,
      required: true
    },
    type: {
      type: String,
      default: 'success',
      validator: (value) => ['success', 'error'].includes(value)
    },
    duration: {
      type: Number,
      default: 3000
    }
  },
  setup(props) {
    const show = ref(true);
    let timeoutId = null;

    const close = () => {
      show.value = false;
      if (timeoutId) {
        clearTimeout(timeoutId);
      }
    };

    watch(() => props.message, () => {
      show.value = true;
      if (timeoutId) {
        clearTimeout(timeoutId);
      }
      timeoutId = setTimeout(close, props.duration);
    });

    return {
      show,
      close
    };
  }
};
</script>

<style lang="scss" scoped>
.notification {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 1rem;
  border-radius: 4px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  z-index: 1000;
  min-width: 300px;
  max-width: 400px;

  &.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }

  &.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }
}

.notification-content {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.notification-icon {
  font-size: 1.25rem;
}

.notification-message {
  flex: 1;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style> 