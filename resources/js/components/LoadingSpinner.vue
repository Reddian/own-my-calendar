<template>
    <div class="loading-spinner" :class="[size, color]">
        <div class="spinner"></div>
        <span v-if="text" class="loading-text">{{ text }}</span>
    </div>
</template>

<script>
export default {
    name: 'LoadingSpinner',
    props: {
        size: {
            type: String,
            default: 'medium',
            validator: (value) => ['small', 'medium', 'large'].includes(value)
        },
        color: {
            type: String,
            default: 'primary',
            validator: (value) => ['primary', 'secondary', 'white'].includes(value)
        },
        text: {
            type: String,
            default: ''
        }
    }
};
</script>

<style scoped>
.loading-spinner {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.spinner {
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* Sizes */
.small .spinner {
    width: 1rem;
    height: 1rem;
    border-width: 2px;
}

.medium .spinner {
    width: 2rem;
    height: 2rem;
    border-width: 3px;
}

.large .spinner {
    width: 3rem;
    height: 3rem;
    border-width: 4px;
}

/* Colors */
.primary .spinner {
    border: solid #e2e8f0;
    border-top-color: #4299e1;
}

.secondary .spinner {
    border: solid #e2e8f0;
    border-top-color: #667eea;
}

.white .spinner {
    border: solid rgba(255, 255, 255, 0.3);
    border-top-color: #ffffff;
}

.loading-text {
    font-size: 0.875rem;
    color: inherit;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style> 