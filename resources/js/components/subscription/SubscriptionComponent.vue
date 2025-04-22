&lt;template&gt;
  &lt;div class="subscription-container"&gt;
    &lt;div class="subscription-header"&gt;
      &lt;h2&gt;Subscription Plan&lt;/h2&gt;
    &lt;/div&gt;
    
    &lt;div v-if="loading" class="loading-indicator"&gt;
      &lt;p&gt;Loading subscription details...&lt;/p&gt;
    &lt;/div&gt;
    
    &lt;div v-else class="subscription-content"&gt;
      &lt;div class="plan-info"&gt;
        &lt;div class="current-plan" :class="{ 'premium-plan': isPremium, 'free-plan': !isPremium }"&gt;
          &lt;div class="plan-badge"&gt;{{ isPremium ? 'Premium' : 'Free' }}&lt;/div&gt;
          &lt;h3&gt;{{ isPremium ? 'Premium Plan' : 'Free Plan' }}&lt;/h3&gt;
          &lt;p v-if="isPremium"&gt;You have unlimited calendar grades with your premium subscription.&lt;/p&gt;
          &lt;p v-else&gt;You have used {{ gradesUsed }} of {{ gradesLimit }} free calendar grades.&lt;/p&gt;
          
          &lt;div v-if="subscription && subscription.trial" class="trial-notice"&gt;
            &lt;p&gt;Your trial ends on {{ formatDate(subscription.trial_ends_at) }}&lt;/p&gt;
          &lt;/div&gt;
          
          &lt;div v-if="subscription && subscription.canceled" class="cancellation-notice"&gt;
            &lt;p&gt;Your subscription will end on {{ formatDate(subscription.ends_at) }}&lt;/p&gt;
          &lt;/div&gt;
        &lt;/div&gt;
        
        &lt;div class="plan-actions"&gt;
          &lt;button 
            v-if="!isPremium" 
            @click="upgradeSubscription" 
            class="btn-upgrade"
            :disabled="isProcessing"
          &gt;
            {{ isProcessing ? 'Processing...' : 'Upgrade to Premium - $9/month' }}
          &lt;/button&gt;
          
          &lt;button 
            v-if="isPremium && !subscription.canceled" 
            @click="cancelSubscription" 
            class="btn-cancel"
            :disabled="isProcessing"
          &gt;
            {{ isProcessing ? 'Processing...' : 'Cancel Subscription' }}
          &lt;/button&gt;
        &lt;/div&gt;
      &lt;/div&gt;
      
      &lt;div class="plan-comparison"&gt;
        &lt;h3&gt;Plan Comparison&lt;/h3&gt;
        &lt;table&gt;
          &lt;thead&gt;
            &lt;tr&gt;
              &lt;th&gt;Feature&lt;/th&gt;
              &lt;th&gt;Free Plan&lt;/th&gt;
              &lt;th&gt;Premium Plan&lt;/th&gt;
            &lt;/tr&gt;
          &lt;/thead&gt;
          &lt;tbody&gt;
            &lt;tr&gt;
              &lt;td&gt;Calendar Grades&lt;/td&gt;
              &lt;td&gt;3 per account&lt;/td&gt;
              &lt;td&gt;Unlimited&lt;/td&gt;
            &lt;/tr&gt;
            &lt;tr&gt;
              &lt;td&gt;Multiple Google Calendars&lt;/td&gt;
              &lt;td&gt;✓&lt;/td&gt;
              &lt;td&gt;✓&lt;/td&gt;
            &lt;/tr&gt;
            &lt;tr&gt;
              &lt;td&gt;Weekly Grade Updates&lt;/td&gt;
              &lt;td&gt;-&lt;/td&gt;
              &lt;td&gt;✓&lt;/td&gt;
            &lt;/tr&gt;
            &lt;tr&gt;
              &lt;td&gt;Weekly Planning Reminders&lt;/td&gt;
              &lt;td&gt;-&lt;/td&gt;
              &lt;td&gt;✓&lt;/td&gt;
            &lt;/tr&gt;
            &lt;tr&gt;
              &lt;td&gt;Price&lt;/td&gt;
              &lt;td&gt;Free&lt;/td&gt;
              &lt;td&gt;$9/month&lt;/td&gt;
            &lt;/tr&gt;
          &lt;/tbody&gt;
        &lt;/table&gt;
      &lt;/div&gt;
    &lt;/div&gt;
  &lt;/div&gt;
&lt;/template&gt;

&lt;script&gt;
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

export default {
  name: 'SubscriptionComponent',
  
  setup() {
    const loading = ref(true);
    const isProcessing = ref(false);
    const subscription = ref(null);
    const gradesUsed = ref(0);
    const gradesLimit = ref(3);
    const error = ref(null);
    
    const isPremium = computed(() => {
      if (!subscription.value) return false;
      return subscription.value.plan === 'premium';
    });
    
    // Fetch subscription details
    const fetchSubscription = async () => {
      loading.value = true;
      try {
        const response = await axios.get('/api/subscription');
        if (response.data.success) {
          subscription.value = response.data.subscription;
          gradesUsed.value = response.data.grades_used;
          gradesLimit.value = response.data.grades_limit;
        } else {
          error.value = response.data.error || 'Failed to load subscription details';
        }
      } catch (err) {
        console.error('Error fetching subscription:', err);
        error.value = 'Failed to load subscription details';
      } finally {
        loading.value = false;
      }
    };
    
    // Upgrade to premium
    const upgradeSubscription = async () => {
      isProcessing.value = true;
      try {
        const response = await axios.post('/api/subscription/checkout');
        if (response.data.success) {
          // Redirect to Stripe checkout
          window.location.href = response.data.checkout_url;
        } else {
          error.value = response.data.error || 'Failed to create checkout session';
          isProcessing.value = false;
        }
      } catch (err) {
        console.error('Error creating checkout session:', err);
        error.value = 'Failed to create checkout session';
        isProcessing.value = false;
      }
    };
    
    // Cancel subscription
    const cancelSubscription = async () => {
      if (!confirm('Are you sure you want to cancel your subscription? You will lose access to premium features at the end of your billing period.')) {
        return;
      }
      
      isProcessing.value = true;
      try {
        const response = await axios.post('/api/subscription/cancel');
        if (response.data.success) {
          await fetchSubscription(); // Refresh subscription data
        } else {
          error.value = response.data.error || 'Failed to cancel subscription';
        }
      } catch (err) {
        console.error('Error cancelling subscription:', err);
        error.value = 'Failed to cancel subscription';
      } finally {
        isProcessing.value = false;
      }
    };
    
    // Format date
    const formatDate = (dateString) => {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
      });
    };
    
    onMounted(() => {
      fetchSubscription();
    });
    
    return {
      loading,
      isProcessing,
      subscription,
      gradesUsed,
      gradesLimit,
      error,
      isPremium,
      upgradeSubscription,
      cancelSubscription,
      formatDate
    };
  }
};
&lt;/script&gt;

&lt;style lang="scss" scoped&gt;
.subscription-container {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  margin-bottom: 2rem;
  
  .subscription-header {
    padding: 1.5rem;
    border-bottom: 1px solid #eee;
    
    h2 {
      margin: 0;
      font-size: 1.5rem;
      color: #333;
    }
  }
  
  .loading-indicator {
    padding: 2rem;
    text-align: center;
    color: #666;
  }
  
  .subscription-content {
    padding: 1.5rem;
  }
  
  .plan-info {
    margin-bottom: 2rem;
    
    .current-plan {
      position: relative;
      padding: 1.5rem;
      border-radius: 8px;
      margin-bottom: 1.5rem;
      
      &.premium-plan {
        background-color: #f0f7ff;
        border: 1px solid #cce5ff;
      }
      
      &.free-plan {
        background-color: #f5f5f5;
        border: 1px solid #e0e0e0;
      }
      
      .plan-badge {
        position: absolute;
        top: -10px;
        right: 20px;
        background-color: #4a90e2;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        
        .free-plan & {
          background-color: #777;
        }
      }
      
      h3 {
        margin-top: 0;
        margin-bottom: 0.5rem;
        font-size: 1.2rem;
      }
      
      p {
        margin-bottom: 0;
        color: #555;
      }
      
      .trial-notice, .cancellation-notice {
        margin-top: 1rem;
        padding: 0.75rem;
        border-radius: 4px;
        font-size: 0.9rem;
      }
      
      .trial-notice {
        background-color: #fff8e1;
        border: 1px solid #ffe082;
      }
      
      .cancellation-notice {
        background-color: #ffebee;
        border: 1px solid #ffcdd2;
      }
    }
  }
  
  .plan-actions {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    
    button {
      padding: 0.75rem 1rem;
      border-radius: 4px;
      font-size: 1rem;
      font-weight: 500;
      cursor: pointer;
      border: none;
      transition: background-color 0.2s;
      
      &:disabled {
        opacity: 0.7;
        cursor: not-allowed;
      }
    }
    
    .btn-upgrade {
      background-color: #4a90e2;
      color: white;
      
      &:hover:not(:disabled) {
        background-color: #3a80d2;
      }
    }
    
    .btn-cancel {
      background-color: #f5f5f5;
      color: #d32f2f;
      border: 1px solid #e0e0e0;
      
      &:hover:not(:disabled) {
        background-color: #ffebee;
      }
    }
  }
  
  .plan-comparison {
    h3 {
      margin-top: 0;
      margin-bottom: 1rem;
      font-size: 1.2rem;
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
      
      th, td {
        padding: 0.75rem;
        text-align: left;
        border-bottom: 1px solid #eee;
      }
      
      th {
        font-weight: 600;
        color: #333;
        background-color: #f9f9f9;
      }
      
      tr:last-child td {
        border-bottom: none;
      }
    }
  }
}
&lt;/style&gt;
