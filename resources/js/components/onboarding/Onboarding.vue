<template>
  <div class="onboarding-container">
    <div class="onboarding-header">
      <h1>Welcome to Own My Calendar</h1>
      <p>Let's set up your profile to help grade your calendar effectively.</p>
    </div>

    <form @submit.prevent="submitOnboarding" class="onboarding-form">
      <div class="form-section">
        <h2>Your Mount Everest</h2>
        <p>What is your ultimate goal or aspiration? This is your "Mount Everest" - the big achievement you're working toward.</p>
        <textarea 
          v-model="formData.mt_everest" 
          rows="4" 
          placeholder="Describe your ultimate goal..."
          required
        ></textarea>
      </div>

      <div class="form-section">
        <h2>Money-Making Activities</h2>
        <p>What are your top 5 money-making activities? These are the tasks that directly contribute to your financial success.</p>
        <textarea 
          v-model="formData.money_making_activities" 
          rows="6" 
          placeholder="List your top 5 money-making activities..."
          required
        ></textarea>
        <p class="examples">Examples: W2 job, real estate investments, affiliate marketing, digital content creation, crypto</p>
      </div>

      <div class="form-section">
        <h2>Energy Renewal Activities</h2>
        <p>What activities help you renew your energy? These are personal activities that recharge you.</p>
        <textarea 
          v-model="formData.energy_renewal_activities" 
          rows="4" 
          placeholder="Describe your energy renewal activities..."
          required
        ></textarea>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary" :disabled="loading">
          {{ loading ? 'Saving...' : 'Complete Onboarding' }}
        </button>
      </div>
    </form>

    <div v-if="error" class="error-message">
      {{ error }}
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';

export default {
  name: 'Onboarding',
  setup() {
    const store = useStore();
    const router = useRouter();
    
    const formData = reactive({
      mt_everest: '',
      money_making_activities: '',
      energy_renewal_activities: '',
      calendar_preferences: {}
    });

    const loading = computed(() => store.getters.loading);
    const error = computed(() => store.getters.error);

    onMounted(async () => {
      // Check if user has already completed onboarding
      await store.dispatch('fetchProfile');
      if (store.getters.hasCompletedOnboarding) {
        router.push({ name: 'dashboard' });
      }
    });

    const submitOnboarding = async () => {
      try {
        await store.dispatch('completeOnboarding', formData);
        router.push({ name: 'dashboard' });
      } catch (err) {
        console.error('Onboarding error:', err);
      }
    };

    return {
      formData,
      loading,
      error,
      submitOnboarding
    };
  }
};
</script>

<style lang="scss" scoped>
.onboarding-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem;
  
  .onboarding-header {
    text-align: center;
    margin-bottom: 2rem;
    
    h1 {
      font-size: 2rem;
      margin-bottom: 0.5rem;
    }
    
    p {
      font-size: 1.1rem;
      color: #666;
    }
  }
  
  .onboarding-form {
    .form-section {
      margin-bottom: 2rem;
      
      h2 {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
      }
      
      p {
        margin-bottom: 1rem;
        color: #555;
      }
      
      textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-family: inherit;
        font-size: 1rem;
        
        &:focus {
          outline: none;
          border-color: #4a90e2;
          box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
        }
      }
      
      .examples {
        font-size: 0.9rem;
        font-style: italic;
        color: #777;
        margin-top: 0.5rem;
      }
    }
    
    .form-actions {
      text-align: center;
      
      .btn-primary {
        background-color: #4a90e2;
        color: white;
        border: none;
        padding: 0.75rem 2rem;
        font-size: 1.1rem;
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
  }
  
  .error-message {
    margin-top: 1rem;
    padding: 0.75rem;
    background-color: #ffebee;
    color: #d32f2f;
    border-radius: 4px;
    text-align: center;
  }
}
</style>
