<template>
  <div class="register-container">
    <div class="register-card">
      <div class="register-header">
        <h1>Own My Calendar</h1>
        <p>Create an account to start grading your calendar</p>
      </div>

      <form @submit.prevent="register" class="register-form">
        <div class="form-group">
          <label for="name">Name</label>
          <input 
            type="text" 
            id="name" 
            v-model="formData.name" 
            required
            placeholder="Enter your name"
          >
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <input 
            type="email" 
            id="email" 
            v-model="formData.email" 
            required
            placeholder="Enter your email"
          >
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input 
            type="password" 
            id="password" 
            v-model="formData.password" 
            required
            placeholder="Create a password"
            minlength="8"
          >
        </div>

        <div class="form-group">
          <label for="password_confirmation">Confirm Password</label>
          <input 
            type="password" 
            id="password_confirmation" 
            v-model="formData.password_confirmation" 
            required
            placeholder="Confirm your password"
            minlength="8"
          >
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary" :disabled="loading">
            {{ loading ? 'Creating account...' : 'Create Account' }}
          </button>
        </div>

        <div class="form-links">
          <router-link :to="{ name: 'login' }">Already have an account? Sign in</router-link>
        </div>
      </form>

      <div v-if="error" class="error-message">
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script>
import { reactive, computed } from 'vue';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';

export default {
  name: 'Register',
  setup() {
    const store = useStore();
    const router = useRouter();
    
    const formData = reactive({
      name: '',
      email: '',
      password: '',
      password_confirmation: ''
    });

    const loading = computed(() => store.getters.loading);
    const error = computed(() => store.getters.error);

    const register = async () => {
      if (formData.password !== formData.password_confirmation) {
        store.commit('SET_ERROR', 'Passwords do not match');
        return;
      }
      
      try {
        await store.dispatch('register', formData);
        router.push({ name: 'onboarding' });
      } catch (err) {
        console.error('Registration error:', err);
      }
    };

    return {
      formData,
      loading,
      error,
      register
    };
  }
};
</script>

<style lang="scss" scoped>
.register-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #f5f7fa;
  
  .register-card {
    width: 100%;
    max-width: 400px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    
    .register-header {
      text-align: center;
      margin-bottom: 2rem;
      
      h1 {
        font-size: 1.8rem;
        margin-bottom: 0.5rem;
        color: #333;
      }
      
      p {
        color: #666;
      }
    }
    
    .register-form {
      .form-group {
        margin-bottom: 1.5rem;
        
        label {
          display: block;
          margin-bottom: 0.5rem;
          font-weight: 500;
          color: #555;
        }
        
        input {
          width: 100%;
          padding: 0.75rem;
          border: 1px solid #ddd;
          border-radius: 4px;
          font-size: 1rem;
          
          &:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
          }
        }
      }
      
      .form-actions {
        margin-bottom: 1.5rem;
        
        .btn-primary {
          width: 100%;
          background-color: #4a90e2;
          color: white;
          border: none;
          padding: 0.75rem;
          font-size: 1rem;
          border-radius: 4px;
          cursor: pointer;
          transition: background-color 0.2s;
          
          &:hover {
            background-color: #3a80d2;
          }
          
          &:disabled {
            background-color: #a0c0e8;
            cursor: not-allowed;
          }
        }
      }
      
      .form-links {
        text-align: center;
        
        a {
          color: #4a90e2;
          text-decoration: none;
          font-size: 0.9rem;
          
          &:hover {
            text-decoration: underline;
          }
        }
      }
    }
    
    .error-message {
      margin-top: 1rem;
      padding: 0.75rem;
      background-color: #ffebee;
      color: #d32f2f;
      border-radius: 4px;
      text-align: center;
      font-size: 0.9rem;
    }
  }
}
</style>
