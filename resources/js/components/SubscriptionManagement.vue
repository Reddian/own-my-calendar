<template>
  <div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-xl font-semibold mb-4">Subscription Management</h2>

    <!-- Current Subscription Status -->
    <div class="mb-6">
      <h3 class="text-lg font-medium mb-2">Current Plan</h3>
      <div class="flex items-center justify-between p-4 border rounded-lg">
        <div>
          <p class="font-medium">{{ currentPlan.name }}</p>
          <p class="text-sm text-gray-500">
            {{ currentPlan.status === 'active' ? 'Active' : 'Inactive' }}
            <span v-if="currentPlan.status === 'active' && currentPlan.cancel_at_period_end">
              (Cancels at period end)
            </span>
          </p>
        </div>
        <div class="text-right">
          <p class="font-medium">${{ currentPlan.amount / 100 }}/{{ currentPlan.interval }}</p>
          <p class="text-sm text-gray-500">
            Next billing: {{ formatDate(currentPlan.current_period_end) }}
          </p>
        </div>
      </div>
    </div>

    <!-- Subscription Actions -->
    <div class="space-y-4">
      <button
        v-if="currentPlan.status === 'active' && !currentPlan.cancel_at_period_end"
        @click="cancelSubscription"
        class="w-full bg-red-500 text-white py-2 px-4 rounded-md hover:bg-red-600 transition-colors"
      >
        Cancel Subscription
      </button>

      <button
        v-if="currentPlan.status === 'canceled' || currentPlan.cancel_at_period_end"
        @click="reactivateSubscription"
        class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 transition-colors"
      >
        Reactivate Subscription
      </button>

      <button
        @click="updateSubscription"
        class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 transition-colors"
      >
        Change Plan
      </button>
    </div>

    <!-- Billing History -->
    <div class="mt-8">
      <h3 class="text-lg font-medium mb-4">Billing History</h3>
      <div v-if="invoices.length > 0" class="space-y-4">
        <div
          v-for="invoice in invoices"
          :key="invoice.id"
          class="flex items-center justify-between p-4 border rounded-lg"
        >
          <div>
            <p class="font-medium">Invoice #{{ invoice.number }}</p>
            <p class="text-sm text-gray-500">{{ formatDate(invoice.created) }}</p>
          </div>
          <div class="text-right">
            <p class="font-medium">${{ invoice.amount_paid / 100 }}</p>
            <p class="text-sm" :class="invoice.status === 'paid' ? 'text-green-500' : 'text-red-500'">
              {{ invoice.status }}
            </p>
          </div>
        </div>
      </div>
      <p v-else class="text-gray-500">No billing history available.</p>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useStore } from 'vuex';

export default {
  name: 'SubscriptionManagement',
  setup() {
    const store = useStore();
    const currentPlan = ref({
      name: '',
      status: '',
      amount: 0,
      interval: '',
      current_period_end: null,
      cancel_at_period_end: false
    });
    const invoices = ref([]);

    onMounted(async () => {
      await loadSubscriptionStatus();
      await loadBillingHistory();
    });

    const loadSubscriptionStatus = async () => {
      try {
        const response = await axios.get('/api/stripe/subscription/status');
        currentPlan.value = response.data;
      } catch (error) {
        store.dispatch('notifications/addNotification', {
          message: 'Failed to load subscription status.',
          type: 'error'
        });
      }
    };

    const loadBillingHistory = async () => {
      try {
        const response = await axios.get('/api/stripe/invoices');
        invoices.value = response.data.invoices;
      } catch (error) {
        store.dispatch('notifications/addNotification', {
          message: 'Failed to load billing history.',
          type: 'error'
        });
      }
    };

    const cancelSubscription = async () => {
      try {
        await axios.post('/api/stripe/subscription/cancel');
        await loadSubscriptionStatus();

        store.dispatch('notifications/addNotification', {
          message: 'Subscription cancelled successfully.',
          type: 'success'
        });
      } catch (error) {
        store.dispatch('notifications/addNotification', {
          message: 'Failed to cancel subscription.',
          type: 'error'
        });
      }
    };

    const reactivateSubscription = async () => {
      try {
        await axios.post('/api/stripe/subscription/reactivate');
        await loadSubscriptionStatus();

        store.dispatch('notifications/addNotification', {
          message: 'Subscription reactivated successfully.',
          type: 'success'
        });
      } catch (error) {
        store.dispatch('notifications/addNotification', {
          message: 'Failed to reactivate subscription.',
          type: 'error'
        });
      }
    };

    const updateSubscription = () => {
      // Navigate to subscription plans page
      router.push('/subscription');
    };

    const formatDate = (timestamp) => {
      return new Date(timestamp * 1000).toLocaleDateString();
    };

    return {
      currentPlan,
      invoices,
      cancelSubscription,
      reactivateSubscription,
      updateSubscription,
      formatDate
    };
  }
};
</script> 