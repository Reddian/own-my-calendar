<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-10">
        <!-- Alert Messages -->
        <div v-if="successMessage" class="alert alert-success alert-dismissible fade show" role="alert">
          {{ successMessage }}
          <button type="button" class="btn-close" @click="successMessage = null" aria-label="Close"></button>
        </div>
        
        <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
          {{ errorMessage }}
          <button type="button" class="btn-close" @click="errorMessage = null" aria-label="Close"></button>
        </div>
        
        <div class="card subscription-card">
          <div class="card-header">
            <h1 class="text-center">Upgrade to Premium</h1>
          </div>
          <div class="card-body">
            <div class="subscription-plans">
              <div class="row">
                <!-- Free Plan -->
                <div class="col-md-6">
                  <div class="plan-card free">
                    <div class="plan-header">
                      <h2>Free Plan</h2>
                      <div class="price">$0<span>/month</span></div>
                    </div>
                    <div class="plan-features">
                      <ul>
                        <li><i class="fas fa-check"></i> Connect 1 calendar</li>
                        <li><i class="fas fa-check"></i> Basic calendar analytics</li>
                        <li><i class="fas fa-check"></i> 3 calendar grades total</li>
                        <li><i class="fas fa-times"></i> Advanced AI recommendations</li>
                        <li><i class="fas fa-times"></i> Priority support</li>
                      </ul>
                    </div>
                    <div class="plan-footer">
                      <p>Your current plan</p>
                      <div class="progress mb-3">
                        <div class="progress-bar bg-primary" role="progressbar" 
                          :style="{ width: (gradesUsed / 3) * 100 + '%' }" 
                          :aria-valuenow="gradesUsed" 
                          aria-valuemin="0" aria-valuemax="3">
                        </div>
                      </div>
                      <p class="grades-remaining">{{ 3 - gradesUsed }} grades remaining</p>
                    </div>
                  </div>
                </div>

                <!-- Premium Plan -->
                <div class="col-md-6">
                  <div class="plan-card premium">
                    <div class="plan-header">
                      <h2>Premium Plan</h2>
                      <div class="billing-toggle">
                        <div class="toggle-container">
                          <span :class="['toggle-label', { active: billingInterval === 'monthly' }]">Monthly</span>
                          <label class="switch">
                            <input 
                              type="checkbox" 
                              v-model="billingInterval" 
                              true-value="yearly" 
                              false-value="monthly"
                              @change="handleBillingChange"
                            >
                            <span class="slider round"></span>
                          </label>
                          <span :class="['toggle-label', { active: billingInterval === 'yearly' }]">Yearly</span>
                        </div>
                      </div>
                      <div :class="['price-display', { active: billingInterval === 'monthly' }]">
                        <div class="price">$9<span>/month</span></div>
                        <div class="price-note">Billed monthly</div>
                      </div>
                      <div :class="['price-display', { active: billingInterval === 'yearly' }]">
                        <div class="price">$89<span>/year</span></div>
                        <div class="price-note">Save $19 per year</div>
                      </div>
                    </div>
                    <div class="plan-features">
                      <ul>
                        <li><i class="fas fa-check"></i> Connect unlimited calendars</li>
                        <li><i class="fas fa-check"></i> Advanced calendar analytics</li>
                        <li><i class="fas fa-check"></i> Unlimited calendar grades</li>
                        <li><i class="fas fa-check"></i> Advanced AI recommendations</li>
                        <li><i class="fas fa-check"></i> Priority support</li>
                      </ul>
                    </div>
                    <div class="plan-footer">
                      <button 
                        @click="startCheckout" 
                        class="btn btn-primary btn-lg btn-block subscribe-btn"
                        :disabled="loading"
                      >
                        {{ loading ? 'Processing...' : 'Upgrade Now' }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Benefits Section -->
            <div class="subscription-benefits mt-5">
              <h2>Why Upgrade to Premium?</h2>
              <div class="row mt-4">
                <div class="col-md-4">
                  <div class="benefit-card">
                    <div class="benefit-icon">
                      <i class="fas fa-infinity"></i>
                    </div>
                    <h3>Unlimited Grades</h3>
                    <p>Grade your calendar as often as you want to continuously improve your time management.</p>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="benefit-card">
                    <div class="benefit-icon">
                      <i class="fas fa-brain"></i>
                    </div>
                    <h3>Advanced AI</h3>
                    <p>Get personalized recommendations to optimize your calendar based on your unique patterns.</p>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="benefit-card">
                    <div class="benefit-icon">
                      <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Detailed Analytics</h3>
                    <p>Access comprehensive insights about how you spend your time across all your calendars.</p>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- FAQ Section -->
            <div class="subscription-faq mt-5">
              <h2>Frequently Asked Questions</h2>
              <div class="accordion mt-4" id="subscriptionFAQ">
                <div class="accordion-item">
                  <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                      Can I cancel my subscription at any time?
                    </button>
                  </h2>
                  <div id="collapseOne" class="accordion-collapse collapse show">
                    <div class="accordion-body">
                      Yes, you can cancel your subscription at any time from your account settings. Your subscription will remain active until the end of your current billing period.
                    </div>
                  </div>
                </div>
                <div class="accordion-item">
                  <h2 class="accordion-header" id="headingTwo">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                      When will I be charged?
                    </button>
                  </h2>
                  <div id="collapseTwo" class="accordion-collapse collapse">
                    <div class="accordion-body">
                      Your subscription will begin immediately upon sign-up. You'll be charged the monthly fee at the start of each billing cycle.
                    </div>
                  </div>
                </div>
                <div class="accordion-item">
                  <h2 class="accordion-header" id="headingThree">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                      What payment methods do you accept?
                    </button>
                  </h2>
                  <div id="collapseThree" class="accordion-collapse collapse">
                    <div class="accordion-body">
                      We accept all major credit cards (Visa, Mastercard, American Express, Discover) as well as PayPal.
                    </div>
                  </div>
                </div>
                <div class="accordion-item">
                  <h2 class="accordion-header" id="headingFour">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                      Is my payment information secure?
                    </button>
                  </h2>
                  <div id="collapseFour" class="accordion-collapse collapse">
                    <div class="accordion-body">
                      Yes, all payment processing is handled securely by Stripe. We never store your full credit card information on our servers.
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading Modal -->
    <div v-if="loading" class="modal fade show" style="display: block;" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-body text-center py-4">
            <div class="spinner-border text-primary mb-3" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <h5>Processing your subscription...</h5>
            <p class="text-muted">Please don't close this window. You'll be redirected to Stripe to complete your payment.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';
import axios from 'axios';

export default {
  name: 'Subscription',
  setup() {
    const store = useStore();
    const router = useRouter();
    const loading = ref(false);
    const billingInterval = ref('monthly');
    const gradesUsed = ref(0);
    const successMessage = ref(null);
    const errorMessage = ref(null);

    onMounted(async () => {
      try {
        // Fetch user's current grades used
        const response = await axios.get('/api/user/grades-used');
        gradesUsed.value = response.data.grades_used;
      } catch (error) {
        errorMessage.value = 'Failed to load subscription information.';
      }
    });

    const handleBillingChange = () => {
      // Force a re-render of the price displays
      billingInterval.value = billingInterval.value;
    };

    const startCheckout = async () => {
      try {
        loading.value = true;
        errorMessage.value = null;
        
        // Create checkout session
        const response = await axios.post('/api/stripe/create-checkout-session', {
          plan: 'premium',
          interval: billingInterval.value,
          success_url: `${window.location.origin}/subscription/success`,
          cancel_url: `${window.location.origin}/subscription`
        });

        // Redirect to Stripe Checkout
        window.location.href = response.data.url;
      } catch (error) {
        errorMessage.value = 'Failed to start checkout. Please try again.';
        loading.value = false;
      }
    };

    return {
      loading,
      billingInterval,
      gradesUsed,
      successMessage,
      errorMessage,
      handleBillingChange,
      startCheckout
    };
  }
};
</script>

<style scoped>
.subscription-card {
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  margin-bottom: 30px;
}

.card-header {
  background: linear-gradient(to right, var(--primary-purple), var(--primary-teal));
  color: white;
  border-top-left-radius: 10px;
  border-top-right-radius: 10px;
  padding: 15px;
}

.plan-card {
  border-radius: 10px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  padding: 20px;
  height: 100%;
  display: flex;
  flex-direction: column;
  margin-bottom: 20px;
}

.plan-card.free {
  border: 2px solid #e0e0e0;
}

.plan-card.premium {
  border: 2px solid var(--primary-purple);
  position: relative;
  overflow: hidden;
}

.plan-card.premium::before {
  content: "RECOMMENDED";
  position: absolute;
  top: 0;
  right: 0;
  background: var(--primary-purple);
  color: white;
  padding: 5px 10px;
  font-size: 12px;
  border-bottom-left-radius: 10px;
}

.plan-header {
  text-align: center;
  margin-bottom: 20px;
}

.price {
  font-size: 2.5rem;
  font-weight: bold;
  color: var(--primary-purple);
}

.price span {
  font-size: 1rem;
  color: #666;
}

.plan-features ul {
  list-style: none;
  padding: 0;
  margin: 0 0 20px 0;
}

.plan-features li {
  margin-bottom: 10px;
  display: flex;
  align-items: center;
}

.plan-features i {
  margin-right: 10px;
  color: var(--primary-purple);
}

.plan-features .fa-times {
  color: #999;
}

.billing-toggle {
  margin: 15px 0;
}

.toggle-container {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.toggle-label {
  color: #666;
  font-size: 0.9rem;
}

.toggle-label.active {
  color: var(--primary-purple);
  font-weight: bold;
}

.switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 24px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 16px;
  width: 16px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: .4s;
}

input:checked + .slider {
  background: linear-gradient(to right, var(--primary-purple), var(--primary-teal));
}

input:checked + .slider:before {
  transform: translateX(26px);
}

.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}

.price-display {
  display: none;
}

.price-display.active {
  display: block;
}

.benefit-card {
  text-align: center;
  padding: 20px;
  border-radius: 10px;
  background: #f8f9fa;
  height: 100%;
}

.benefit-icon {
  font-size: 2.5rem;
  color: var(--primary-purple);
  margin-bottom: 15px;
}

.accordion-button:not(.collapsed) {
  background-color: var(--primary-purple);
  color: white;
}

.accordion-button:focus {
  box-shadow: none;
  border-color: var(--primary-purple);
}

.modal {
  background-color: rgba(0, 0, 0, 0.5);
}
</style> 