<template>
  <div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-xl font-semibold mb-4">Payment Methods</h2>
    
    <!-- Add New Payment Method -->
    <div class="mb-6">
      <button
        @click="showAddPaymentModal = true"
        class="flex items-center text-blue-500 hover:text-blue-600"
      >
        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Add Payment Method
      </button>
    </div>

    <!-- Payment Methods List -->
    <div v-if="paymentMethods.length > 0" class="space-y-4">
      <div
        v-for="method in paymentMethods"
        :key="method.id"
        class="flex items-center justify-between p-4 border rounded-lg"
        :class="{ 'border-blue-500': method.is_default }"
      >
        <div class="flex items-center">
          <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center mr-4">
            <svg class="h-6 w-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
          </div>
          <div>
            <p class="font-medium">{{ method.card.brand }} ending in {{ method.card.last4 }}</p>
            <p class="text-sm text-gray-500">Expires {{ method.card.exp_month }}/{{ method.card.exp_year }}</p>
          </div>
        </div>
        <div class="flex items-center space-x-4">
          <button
            v-if="!method.is_default"
            @click="setDefaultPaymentMethod(method.id)"
            class="text-blue-500 hover:text-blue-600"
          >
            Set as Default
          </button>
          <button
            @click="removePaymentMethod(method.id)"
            class="text-red-500 hover:text-red-600"
          >
            Remove
          </button>
        </div>
      </div>
    </div>
    <p v-else class="text-gray-500">No payment methods added yet.</p>

    <!-- Add Payment Method Modal -->
    <div v-if="showAddPaymentModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center">
      <div class="bg-white rounded-lg p-8 max-w-md w-full">
        <h3 class="text-xl font-bold mb-4">Add Payment Method</h3>
        <div id="payment-element" class="mb-4"></div>
        <button
          @click="handleAddPaymentMethod"
          class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 transition-colors"
          :disabled="processingPayment"
        >
          {{ processingPayment ? 'Processing...' : 'Add Payment Method' }}
        </button>
        <button
          @click="showAddPaymentModal = false"
          class="mt-4 w-full bg-gray-500 text-white py-2 px-4 rounded-md hover:bg-gray-600 transition-colors"
        >
          Cancel
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { loadStripe } from '@stripe/stripe-js';
import { useStore } from 'vuex';

export default {
  name: 'PaymentMethods',
  setup() {
    const store = useStore();
    const stripe = ref(null);
    const elements = ref(null);
    const showAddPaymentModal = ref(false);
    const processingPayment = ref(false);
    const paymentMethods = ref([]);

    onMounted(async () => {
      // Initialize Stripe
      stripe.value = await loadStripe(import.meta.env.VITE_STRIPE_PUBLIC_KEY);
      // Load payment methods
      await loadPaymentMethods();
    });

    const loadPaymentMethods = async () => {
      try {
        const response = await axios.get('/api/stripe/payment-methods');
        paymentMethods.value = response.data.payment_methods;
      } catch (error) {
        store.dispatch('notifications/addNotification', {
          message: 'Failed to load payment methods.',
          type: 'error'
        });
      }
    };

    const startAddPaymentMethod = async () => {
      try {
        showAddPaymentModal.value = true;

        // Create setup intent
        const response = await axios.post('/api/stripe/setup-intent');
        const { clientSecret } = response.data;

        // Initialize Elements
        elements.value = stripe.value.elements({ clientSecret });
        const paymentElement = elements.value.create('payment');
        paymentElement.mount('#payment-element');
      } catch (error) {
        store.dispatch('notifications/addNotification', {
          message: 'Failed to initialize payment method addition.',
          type: 'error'
        });
      }
    };

    const handleAddPaymentMethod = async () => {
      try {
        processingPayment.value = true;
        const { error } = await stripe.value.confirmPayment({
          elements: elements.value
        });

        if (error) {
          throw error;
        }

        // Reload payment methods
        await loadPaymentMethods();

        store.dispatch('notifications/addNotification', {
          message: 'Payment method added successfully!',
          type: 'success'
        });

        showAddPaymentModal.value = false;
      } catch (error) {
        store.dispatch('notifications/addNotification', {
          message: error.message || 'Failed to add payment method.',
          type: 'error'
        });
      } finally {
        processingPayment.value = false;
      }
    };

    const setDefaultPaymentMethod = async (paymentMethodId) => {
      try {
        await axios.post('/api/stripe/payment-method', {
          payment_method_id: paymentMethodId
        });

        // Reload payment methods
        await loadPaymentMethods();

        store.dispatch('notifications/addNotification', {
          message: 'Default payment method updated successfully!',
          type: 'success'
        });
      } catch (error) {
        store.dispatch('notifications/addNotification', {
          message: 'Failed to update default payment method.',
          type: 'error'
        });
      }
    };

    const removePaymentMethod = async (paymentMethodId) => {
      try {
        await axios.delete(`/api/stripe/payment-methods/${paymentMethodId}`);

        // Reload payment methods
        await loadPaymentMethods();

        store.dispatch('notifications/addNotification', {
          message: 'Payment method removed successfully!',
          type: 'success'
        });
      } catch (error) {
        store.dispatch('notifications/addNotification', {
          message: 'Failed to remove payment method.',
          type: 'error'
        });
      }
    };

    return {
      showAddPaymentModal,
      processingPayment,
      paymentMethods,
      startAddPaymentMethod,
      handleAddPaymentMethod,
      setDefaultPaymentMethod,
      removePaymentMethod
    };
  }
};
</script> 