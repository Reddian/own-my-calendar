<template>
  <div class="container page-container">
    <!-- Page Heading -->
    <h1 class="page-heading">Settings</h1>

    <div class="row justify-content-center">
      <div class="col-md-10">
        <!-- Alert Messages -->
        <div v-if="successMessage" class="alert alert-success alert-dismissible fade show" role="alert">
          {{ successMessage }}
          <button type="button" class="btn-close" @click="successMessage = ''" aria-label="Close"></button>
        </div>
        <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
          {{ errorMessage }}
          <button type="button" class="btn-close" @click="errorMessage = ''" aria-label="Close"></button>
        </div>
        <!-- Google Specific Messages -->
        <div v-if="googleSuccessMessage" class="alert alert-success alert-dismissible fade show" role="alert">
          {{ googleSuccessMessage }}
          <button type="button" class="btn-close" @click="clearGoogleMessages" aria-label="Close"></button>
        </div>
        <div v-if="googleErrorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
          {{ googleErrorMessage }}
          <button type="button" class="btn-close" @click="clearGoogleMessages" aria-label="Close"></button>
        </div>

        <div class="card settings-card">
          <div class="card-body">
            <ul class="nav nav-tabs settings-tabs" id="settingsTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link" :class="{ active: activeTab === 'account' }" @click="setActiveTab('account')" type="button">Account</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" :class="{ active: activeTab === 'notifications' }" @click="setActiveTab('notifications')" type="button">Notifications</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" :class="{ active: activeTab === 'subscription' }" @click="setActiveTab('subscription')" type="button">Subscription</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" :class="{ active: activeTab === 'calendar' }" @click="setActiveTab('calendar')" type="button">Calendar Integration</button>
              </li>
            </ul>

            <div class="tab-content pt-3 settings-tab-content" id="settingsTabsContent">
              <!-- Account Settings -->
              <div class="tab-pane fade" :class="{ 'show active': activeTab === 'account' }">
                <h3>Account Information</h3>
                <form @submit.prevent="updateAccount">
                  <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" v-model="accountForm.name" :disabled="isUpdatingAccount">
                  </div>
                  <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" v-model="accountForm.email" :disabled="isUpdatingAccount">
                  </div>
                  <!-- Timezone Dropdown -->
                  <div class="mb-3">
                    <label for="timezone" class="form-label">Timezone</label>
                    <select class="form-select" id="timezone" v-model="accountForm.timezone" :disabled="isUpdatingAccount || isLoadingTimezones">
                      <option v-if="isLoadingTimezones" value="" disabled>Loading timezones...</option>
                      <option v-else value="">Select Timezone</option>
                      <option v-for="tz in timezones" :key="tz" :value="tz">{{ tz }}</option>
                    </select>
                  </div>
                  <button type="submit" class="btn btn-primary" :disabled="isUpdatingAccount">
                    <span v-if="isUpdatingAccount" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    {{ isUpdatingAccount ? 'Updating...' : 'Update Account' }}
                  </button>
                </form>

                <hr class="my-4">

                <h3>Change Password</h3>
                <form @submit.prevent="updatePassword">
                  <div class="mb-3">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" class="form-control" id="current_password" v-model="passwordForm.current_password" :disabled="isUpdatingPassword">
                  </div>
                  <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="password" v-model="passwordForm.password" :disabled="isUpdatingPassword">
                  </div>
                  <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="password_confirmation" v-model="passwordForm.password_confirmation" :disabled="isUpdatingPassword">
                  </div>
                  <button type="submit" class="btn btn-primary" :disabled="isUpdatingPassword">
                     <span v-if="isUpdatingPassword" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    {{ isUpdatingPassword ? 'Updating...' : 'Update Password' }}
                  </button>
                </form>
              </div>

              <!-- Notification Settings -->
              <div class="tab-pane fade" :class="{ 'show active': activeTab === 'notifications' }">
                <h3>Notification Preferences</h3>
                <form @submit.prevent="updateNotifications">
                  <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="weekly_grade_email" v-model="notificationForm.weekly_grade_email" :disabled="isUpdatingNotifications">
                    <label class="form-check-label" for="weekly_grade_email">Send me weekly grade emails</label>
                  </div>
                  <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="planning_reminder" v-model="notificationForm.planning_reminder" :disabled="isUpdatingNotifications">
                    <label class="form-check-label" for="planning_reminder">Send me weekly planning reminders</label>
                  </div>
                  <div class="mb-3">
                    <label for="reminder_day" class="form-label">Reminder Day</label>
                    <select class="form-select" id="reminder_day" v-model="notificationForm.reminder_day" :disabled="isUpdatingNotifications">
                      <option value="Sunday">Sunday</option>
                      <option value="Monday">Monday</option>
                      <option value="Tuesday">Tuesday</option>
                      <option value="Wednesday">Wednesday</option>
                      <option value="Thursday">Thursday</option>
                      <option value="Friday">Friday</option>
                      <option value="Saturday">Saturday</option>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label for="reminder_time" class="form-label">Reminder Time</label>
                    <input type="time" class="form-control" id="reminder_time" v-model="notificationForm.reminder_time" :disabled="isUpdatingNotifications">
                  </div>
                  <button type="submit" class="btn btn-primary" :disabled="isUpdatingNotifications">
                    <span v-if="isUpdatingNotifications" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    {{ isUpdatingNotifications ? 'Saving...' : 'Save Notification Settings' }}
                  </button>
                </form>
              </div>

              <!-- Subscription Settings -->
              <div class="tab-pane fade" :class="{ 'show active': activeTab === 'subscription' }">
                <h3>Subscription Status</h3>
                <div v-if="isLoadingSubscription" class="text-center my-3">
                    <div class="spinner-border text-primary" role="status">
                      <span class="visually-hidden">Loading subscription status...</span>
                    </div>
                </div>
                <div v-else-if="subscriptionError" class="alert alert-warning">
                    {{ subscriptionError }}
                </div>
                <div v-else>
                  <div class="card mb-4">
                    <div class="card-body">
                      <div v-if="subscriptionStatus.isActive" class="subscription-status active">
                        <div class="status-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="status-details">
                          <h4>{{ subscriptionStatus.planName || 'Premium Plan' }}</h4>
                          <p>Your subscription is active.</p>
                          <p v-if="subscriptionStatus.nextBillingDate" class="text-muted">Next billing date: {{ subscriptionStatus.nextBillingDate }}</p>
                          <p v-if="subscriptionStatus.cancelAtPeriodEnd" class="text-warning">Cancels on: {{ subscriptionStatus.cancelAtDate }}</p>
                        </div>
                      </div>
                      <div v-else class="subscription-status inactive">
                        <div class="status-icon"><i class="fas fa-info-circle"></i></div>
                        <div class="status-details">
                          <h4>{{ subscriptionStatus.planName || 'Free Plan' }}</h4>
                          <p>You are currently on the free plan.</p>
                          <p v-if="subscriptionStatus.gradesRemaining !== null" class="text-muted">{{ subscriptionStatus.gradesRemaining }} grades remaining</p>
                        </div>
                      </div>

                      <div class="mt-4">
                        <button v-if="subscriptionStatus.isActive && !subscriptionStatus.cancelAtPeriodEnd" type="button" class="btn btn-outline-danger" @click="openCancelModal">
                          Cancel Subscription
                        </button>
                        <router-link v-else-if="!subscriptionStatus.isActive" to="/subscription" class="btn btn-primary">Upgrade to Premium</router-link>
                        <!-- Add button to manage billing/reactivate if needed -->
                      </div>
                    </div>
                  </div>

                  <div v-if="subscriptionStatus.isActive" class="card">
                    <div class="card-header"><h5>Subscription Benefits</h5></div>
                    <div class="card-body">
                      <ul class="subscription-benefits">
                        <li><i class="fas fa-check text-success"></i> Connect unlimited calendars</li>
                        <li><i class="fas fa-check text-success"></i> Unlimited calendar grades</li>
                        <li><i class="fas fa-check text-success"></i> Advanced AI recommendations</li>
                        <li><i class="fas fa-check text-success"></i> Priority support</li>
                        <li><i class="fas fa-check text-success"></i> Detailed analytics</li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Calendar Integration Settings -->
              <div class="tab-pane fade" :class="{ 'show active': activeTab === 'calendar' }" id="calendar-integration">
                <h3>Google Calendar Integration</h3>
                <div class="card mb-4">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <h5>Connect Your Calendar</h5>
                        <p class="text-muted">Connect your Google Calendar to start grading and improving your schedule.</p>
                      </div>
                      <button v-if="!isGoogleConnected" type="button" class="btn btn-primary" @click="connectGoogleCalendar">
                        Connect Calendar
                      </button>
                      <button v-else type="button" class="btn btn-outline-danger" @click="disconnectGoogleCalendar">
                        Disconnect Calendar
                      </button>
                    </div>
                  </div>
                </div>

                <div class="connected-calendars">
                  <h5>Connected Calendars</h5>
                  <p class="text-muted">Select the calendars you want to include in your analysis.</p>
                  <div v-if="isLoadingCalendars" class="text-center my-3">
                    <div class="spinner-border text-primary" role="status">
                      <span class="visually-hidden">Loading calendars...</span>
                    </div>
                  </div>
                  <div v-else-if="calendarFetchError" class="alert alert-warning">
                    {{ calendarFetchError }}
                  </div>
                  <div v-else-if="!isGoogleConnected || connectedCalendars.length === 0" class="alert alert-info">
                    No calendars connected yet. Click the "Connect Calendar" button to get started.
                  </div>
                  <ul v-else class="list-group">
                    <li v-for="calendar in connectedCalendars" :key="calendar.id" class="list-group-item d-flex justify-content-between align-items-center">
                      <div>
                        <span :style="{ color: calendar.backgroundColor }">■</span>
                        {{ calendar.summary }}
                        <span v-if="calendar.primary" class="badge bg-secondary ms-2">Primary</span>
                      </div>
                      <div class="form-check form-switch">
                        <input
                          class="form-check-input"
                          type="checkbox"
                          role="switch"
                          :id="'calendar-select-' + calendar.id"
                          :checked="calendar.is_selected"
                          @change="toggleCalendarSelection(calendar)"
                        >
                        <label class="form-check-label" :for="'calendar-select-' + calendar.id">Include</label>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Cancel Subscription Modal -->
    <div class="modal fade" id="cancelSubscriptionModal" tabindex="-1" ref="cancelModal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Cancel Subscription</h5>
            <button type="button" class="btn-close" @click="closeCancelModal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to cancel your subscription?</p>
            <p>You will still have access to premium features until the end of your current billing period.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeCancelModal">Keep Subscription</button>
            <button type="button" class="btn btn-danger" @click="confirmCancelSubscription" :disabled="isCancelling">
              <span v-if="isCancelling" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              Yes, Cancel Subscription
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useStore } from 'vuex';
import axios from 'axios';
import { Modal } from 'bootstrap';

const route = useRoute();
const router = useRouter();
const store = useStore();

const activeTab = ref('account');

// --- General Messages ---
const successMessage = ref('');
const errorMessage = ref('');

// --- Account Settings ---
const accountForm = reactive({
  name: '',
  email: '',
  timezone: '', // Add timezone
});
const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
});
const isUpdatingAccount = ref(false);
const isUpdatingPassword = ref(false);
const timezones = ref([]); // Add timezones list
const isLoadingTimezones = ref(false);

// --- Notification Settings ---
const notificationForm = reactive({
  weekly_grade_email: false,
  planning_reminder: false,
  reminder_day: 'Sunday',
  reminder_time: '18:00',
});
const isUpdatingNotifications = ref(false);

// --- Subscription Settings ---
const subscriptionStatus = ref({});
const isLoadingSubscription = ref(false);
const subscriptionError = ref('');
const cancelModal = ref(null);
let bsCancelModal = null;
const isCancelling = ref(false);

// --- Calendar Integration ---
const isGoogleConnected = ref(false);
const connectedCalendars = ref([]);
const isLoadingCalendars = ref(false);
const calendarFetchError = ref('');
const googleSuccessMessage = ref('');
const googleErrorMessage = ref('');

// --- Computed Properties ---
const user = computed(() => store.state.user.user);

// --- Methods ---
const setActiveTab = (tab) => {
  activeTab.value = tab;
  // Clear messages when switching tabs
  successMessage.value = '';
  errorMessage.value = '';
  googleSuccessMessage.value = '';
  googleErrorMessage.value = '';

  // Fetch data for the activated tab if needed
  if (tab === 'subscription') {
    fetchSubscriptionStatus();
  } else if (tab === 'calendar') {
    checkGoogleConnection();
  } else if (tab === 'notifications') {
    fetchNotificationSettings();
  } else if (tab === 'account') {
    fetchTimezones(); // Fetch timezones when account tab is active
  }
};

const clearMessages = () => {
  successMessage.value = '';
  errorMessage.value = '';
};

const clearGoogleMessages = () => {
  googleSuccessMessage.value = '';
  googleErrorMessage.value = '';
};

// --- Account Methods ---
const fetchTimezones = async () => {
  if (timezones.value.length > 0) return; // Don't fetch if already loaded
  isLoadingTimezones.value = true;
  try {
    const response = await axios.get('/api/timezones');
    timezones.value = response.data.timezones;
  } catch (error) {
    console.error('Error fetching timezones:', error);
    errorMessage.value = 'Could not load timezones.';
  } finally {
    isLoadingTimezones.value = false;
  }
};

const updateAccount = async () => {
  clearMessages();
  isUpdatingAccount.value = true;
  try {
    const response = await axios.put('/api/profile', accountForm);
    successMessage.value = response.data.message;
    // Update user data in Vuex store
    store.commit('user/SET_USER', response.data.user);
  } catch (error) {
    if (error.response && error.response.data && error.response.data.message) {
      errorMessage.value = error.response.data.message;
    } else {
      errorMessage.value = 'Failed to update account. Please try again.';
    }
    console.error('Account update error:', error);
  } finally {
    isUpdatingAccount.value = false;
  }
};

const updatePassword = async () => {
  clearMessages();
  isUpdatingPassword.value = true;
  try {
    const response = await axios.put('/api/password', passwordForm);
    successMessage.value = response.data.message;
    // Clear password fields on success
    passwordForm.current_password = '';
    passwordForm.password = '';
    passwordForm.password_confirmation = '';
  } catch (error) {
    if (error.response && error.response.data && error.response.data.message) {
      errorMessage.value = error.response.data.message;
    } else if (error.response && error.response.data && error.response.data.errors) {
      // Handle validation errors
      const errors = error.response.data.errors;
      errorMessage.value = Object.values(errors).flat().join(' ');
    } else {
      errorMessage.value = 'Failed to update password. Please try again.';
    }
    console.error('Password update error:', error);
  } finally {
    isUpdatingPassword.value = false;
  }
};

// --- Notification Methods ---
const fetchNotificationSettings = async () => {
  clearMessages();
  isUpdatingNotifications.value = true; // Use loading state
  try {
    const response = await axios.get('/api/notifications/settings');
    const settings = response.data.settings || {};
    notificationForm.weekly_grade_email = settings.weekly_grade_email || false;
    notificationForm.planning_reminder = settings.planning_reminder || false;
    notificationForm.reminder_day = settings.reminder_day || 'Sunday';
    notificationForm.reminder_time = settings.reminder_time || '18:00';
    console.log('Fetched notification settings:', settings);
  } catch (error) {
    errorMessage.value = 'Could not load notification settings.';
    console.error('Error fetching notification settings:', error);
  } finally {
    isUpdatingNotifications.value = false;
  }
};

const updateNotifications = async () => {
  clearMessages();
  isUpdatingNotifications.value = true;
  try {
    const response = await axios.put('/api/notifications/settings', notificationForm);
    successMessage.value = response.data.message;
  } catch (error) {
    if (error.response && error.response.data && error.response.data.message) {
      errorMessage.value = error.response.data.message;
    } else {
      errorMessage.value = 'Failed to update notification settings. Please try again.';
    }
    console.error('Notification update error:', error);
  } finally {
    isUpdatingNotifications.value = false;
  }
};

// --- Subscription Methods ---
const fetchSubscriptionStatus = async () => {
  isLoadingSubscription.value = true;
  subscriptionError.value = '';
  try {
    const response = await axios.get('/api/subscription/status');
    subscriptionStatus.value = response.data;
  } catch (error) {
    console.error('Error fetching subscription status:', error);
    subscriptionError.value = 'Could not load subscription status.';
  } finally {
    isLoadingSubscription.value = false;
  }
};

const openCancelModal = () => {
  if (bsCancelModal) {
    bsCancelModal.show();
  }
};

const closeCancelModal = () => {
  if (bsCancelModal) {
    bsCancelModal.hide();
  }
};

const confirmCancelSubscription = async () => {
  isCancelling.value = true;
  clearMessages();
  try {
    const response = await axios.post('/api/subscription/cancel');
    successMessage.value = response.data.message;
    fetchSubscriptionStatus(); // Refresh status after cancellation
    closeCancelModal();
  } catch (error) {
    if (error.response && error.response.data && error.response.data.message) {
      errorMessage.value = error.response.data.message;
    } else {
      errorMessage.value = 'Failed to cancel subscription. Please try again.';
    }
    console.error('Subscription cancellation error:', error);
  } finally {
    isCancelling.value = false;
  }
};

// --- Calendar Integration Methods ---
const checkGoogleConnection = async () => {
  isLoadingCalendars.value = true;
  calendarFetchError.value = '';
  try {
    const response = await axios.get('/api/calendars/check-connection');
    isGoogleConnected.value = response.data.isConnected;
    if (isGoogleConnected.value) {
      fetchConnectedCalendars();
    } else {
        isLoadingCalendars.value = false;
    }
  } catch (error) {
    console.error('Error checking Google connection:', error);
    calendarFetchError.value = 'Could not check Google Calendar connection status.';
    isLoadingCalendars.value = false;
  }
};

const connectGoogleCalendar = async () => {
  try {
    const response = await axios.get('/api/calendars/auth');
    window.location.href = response.data.authUrl;
  } catch (error) {
    console.error('Error getting Google Auth URL:', error);
    googleErrorMessage.value = 'Could not initiate Google Calendar connection.';
  }
};

const disconnectGoogleCalendar = async () => {
  clearGoogleMessages();
  try {
    await axios.post('/api/calendars/disconnect-all'); // Assuming disconnect-all is appropriate
    isGoogleConnected.value = false;
    connectedCalendars.value = [];
    googleSuccessMessage.value = 'Google Calendar disconnected successfully.';
  } catch (error) {
    console.error('Error disconnecting Google Calendar:', error);
    googleErrorMessage.value = 'Failed to disconnect Google Calendar.';
  }
};

const fetchConnectedCalendars = async () => {
  isLoadingCalendars.value = true;
  calendarFetchError.value = '';
  try {
    const response = await axios.get('/api/calendars');
    connectedCalendars.value = response.data.calendars;
  } catch (error) {
    console.error('Error fetching connected calendars:', error);
    calendarFetchError.value = 'Could not load connected calendars.';
  } finally {
    isLoadingCalendars.value = false;
  }
};

const toggleCalendarSelection = async (calendar) => {
  const newSelectionState = !calendar.is_selected;
  clearGoogleMessages();
  try {
    await axios.post('/api/calendars/selection', {
      calendar_id: calendar.id,
      is_selected: newSelectionState,
    });
    // Update local state immediately for responsiveness
    const index = connectedCalendars.value.findIndex(c => c.id === calendar.id);
    if (index !== -1) {
      connectedCalendars.value[index].is_selected = newSelectionState;
    }
    googleSuccessMessage.value = `Calendar '${calendar.summary}' ${newSelectionState ? 'included' : 'excluded'}.`;
  } catch (error) {
    console.error('Error updating calendar selection:', error);
    googleErrorMessage.value = `Failed to update selection for calendar '${calendar.summary}'.`;
    // Revert local state on error?
  }
};

// --- Lifecycle Hooks ---
onMounted(() => {
  // Initialize Bootstrap Modal
  if (cancelModal.value) {
    bsCancelModal = new Modal(cancelModal.value);
  }

  // Set initial user data in forms
  if (user.value) {
    accountForm.name = user.value.name;
    accountForm.email = user.value.email;
    accountForm.timezone = user.value.timezone || ''; // Load timezone
  }

  // Check for Google callback parameters
  const urlParams = new URLSearchParams(window.location.search);
  const googleStatus = urlParams.get('google_status');
  const googleMessage = urlParams.get('google_message');

  if (googleStatus === 'success') {
    googleSuccessMessage.value = googleMessage || 'Google Calendar connected successfully!';
    setActiveTab('calendar'); // Switch to calendar tab on success
  } else if (googleStatus === 'error') {
    googleErrorMessage.value = googleMessage || 'Failed to connect Google Calendar.';
    setActiveTab('calendar'); // Switch to calendar tab on error
  }

  // Clean the URL
  if (googleStatus) {
    router.replace({ query: {} });
  }

  // Fetch data for the initially active tab
  setActiveTab(activeTab.value);
});

// Watch for changes in user data (e.g., after login) and update form
watch(user, (newUser) => {
  if (newUser) {
    accountForm.name = newUser.name;
    accountForm.email = newUser.email;
    accountForm.timezone = newUser.timezone || ''; // Update timezone on user change
  }
});

</script>

<style scoped>
.page-container {
  padding-top: 2rem; /* Match Extension page spacing */
  padding-bottom: 2rem;
}

.page-heading {
  margin-bottom: 1.5rem; /* Match Extension page spacing */
  font-weight: 300;
}

.settings-card {
  border: none;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
}

.settings-tabs {
  border-bottom: 1px solid #dee2e6;
}

.settings-tabs .nav-link {
  color: #6c757d; /* Default tab text color */
  border: none;
  border-bottom: 2px solid transparent;
  margin-bottom: -1px; /* Overlap border */
  padding: 0.75rem 1.25rem;
  font-weight: 500;
}

.settings-tabs .nav-link.active {
  color: #495057; /* Active tab text color */
  border-bottom-color: #6f42c1; /* Use a theme color for the active indicator */
  background-color: transparent;
}

.settings-tabs .nav-link:hover {
  border-bottom-color: #e9ecef;
}

.settings-tab-content {
  padding: 1.5rem;
}

.settings-tab-content h3 {
  margin-bottom: 1.5rem;
  font-weight: 400;
}

.form-label {
  font-weight: 500;
}

.subscription-status {
  display: flex;
  align-items: center;
  padding: 1rem;
  border-radius: 0.25rem;
}

.subscription-status.active {
  background-color: #e8f5e9; /* Light green background */
  border-left: 5px solid #4caf50; /* Green border */
}

.subscription-status.inactive {
  background-color: #f8f9fa; /* Light grey background */
  border-left: 5px solid #adb5bd; /* Grey border */
}

.status-icon {
  font-size: 1.8rem;
  margin-right: 1rem;
}

.subscription-status.active .status-icon {
  color: #4caf50; /* Green icon */
}

.subscription-status.inactive .status-icon {
  color: #6c757d; /* Grey icon */
}

.status-details h4 {
  margin-bottom: 0.25rem;
}

.status-details p {
  margin-bottom: 0.25rem;
}

.subscription-benefits {
  list-style: none;
  padding-left: 0;
}

.subscription-benefits li {
  margin-bottom: 0.5rem;
}

.subscription-benefits i {
  margin-right: 0.5rem;
}

.connected-calendars .list-group-item {
  padding: 0.75rem 1.25rem;
}

.connected-calendars .form-check-input {
  cursor: pointer;
}

/* Ensure alerts have enough contrast */
.alert-success {
    color: #0f5132;
    background-color: #d1e7dd;
    border-color: #badbcc;
}
.alert-danger {
    color: #842029;
    background-color: #f8d7da;
    border-color: #f5c2c7;
}
.alert-warning {
    color: #664d03;
    background-color: #fff3cd;
    border-color: #ffecb5;
}
.alert-info {
    color: #055160;
    background-color: #cff4fc;
    border-color: #b6effb;
}

</style>

