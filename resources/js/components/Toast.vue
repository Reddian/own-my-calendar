<template>
    <transition name="toast">
        <div v-if="show" class="toast" :class="type" @click="close">
            <div class="toast-content">
                <div class="toast-icon">
                    <i v-if="type === 'success'" class="fas fa-check-circle"></i>
                    <i v-else-if="type === 'error'" class="fas fa-exclamation-circle"></i>
                    <i v-else-if="type === 'warning'" class="fas fa-exclamation-triangle"></i>
                    <i v-else class="fas fa-info-circle"></i>
                </div>
                <div class="toast-message">
                    <p class="toast-title">{{ title }}</p>
                    <p v-if="message" class="toast-text">{{ message }}</p>
                </div>
                <button class="toast-close" @click.stop="close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </transition>
</template>

<script>
export default {
    name: 'Toast',
    props: {
        show: {
            type: Boolean,
            default: false
        },
        type: {
            type: String,
            default: 'info',
            validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
        },
        title: {
            type: String,
            required: true
        },
        message: {
            type: String,
            default: ''
        },
        duration: {
            type: Number,
            default: 5000
        }
    },
    watch: {
        show(newVal) {
            if (newVal) {
                this.startTimer();
            }
        }
    },
    methods: {
        close() {
            this.$emit('close');
        },
        startTimer() {
            if (this.duration > 0) {
                setTimeout(() => {
                    this.close();
                }, this.duration);
            }
        }
    }
};
</script>

<style scoped>
.toast {
    position: fixed;
    top: 1rem;
    right: 1rem;
    min-width: 300px;
    max-width: 400px;
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    z-index: 9999;
    cursor: pointer;
    transition: all 0.3s ease;
}

.toast-content {
    display: flex;
    align-items: center;
    padding: 1rem;
}

.toast-icon {
    margin-right: 1rem;
    font-size: 1.5rem;
}

.toast-message {
    flex: 1;
}

.toast-title {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.toast-text {
    color: #6b7280;
    font-size: 0.875rem;
}

.toast-close {
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    padding: 0.25rem;
    margin-left: 1rem;
}

.toast-close:hover {
    color: #4b5563;
}

/* Type-specific styles */
.success {
    border-left: 4px solid #10b981;
}

.success .toast-icon {
    color: #10b981;
}

.error {
    border-left: 4px solid #ef4444;
}

.error .toast-icon {
    color: #ef4444;
}

.warning {
    border-left: 4px solid #f59e0b;
}

.warning .toast-icon {
    color: #f59e0b;
}

.info {
    border-left: 4px solid #3b82f6;
}

.info .toast-icon {
    color: #3b82f6;
}

/* Animation */
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateX(30px);
}
</style> 