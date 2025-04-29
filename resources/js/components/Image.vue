<template>
    <div class="image-container" :class="{ 'loading': isLoading }">
        <img
            v-if="src"
            :src="src"
            :alt="alt"
            :class="['lazy-image', { 'loaded': !isLoading }]"
            @load="onLoad"
            @error="onError"
            loading="lazy"
        />
        <div v-if="isLoading" class="image-placeholder">
            <div class="spinner"></div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Image',
    props: {
        src: {
            type: String,
            required: true
        },
        alt: {
            type: String,
            default: ''
        },
        width: {
            type: [Number, String],
            default: null
        },
        height: {
            type: [Number, String],
            default: null
        }
    },
    data() {
        return {
            isLoading: true,
            hasError: false
        };
    },
    methods: {
        onLoad() {
            this.isLoading = false;
        },
        onError() {
            this.isLoading = false;
            this.hasError = true;
            this.$emit('error');
        }
    }
};
</script>

<style scoped>
.image-container {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

.lazy-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
}

.lazy-image.loaded {
    opacity: 1;
}

.image-placeholder {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
}

.spinner {
    width: 24px;
    height: 24px;
    border: 3px solid #e2e8f0;
    border-top-color: #4299e1;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style> 