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

// --- Google Callback Messages ---
const googleSuccessMessage = ref('');
const googleErrorMessage = ref('');

// --- Form Data & States ---
const accountForm = reactive({ name: '', email: '' });
const passwordForm = reactive({ current_password: '', password: '', password_confirmation: '' });
const notificationForm = reactive({
  weekly_grade_email: true, // Default values
  planning_reminder: true,
  reminder_day: 'Sunday',
  reminder_time: '18:00'
});
const isUpdatingAccount = ref(false);
const isUpdatingPassword = ref(false);
const isUpdatingNotifications = ref(false);

// --- Subscription Data & State ---
const subscriptionStatus = reactive({
  isActive: false,
  planName: null,
  nextBillingDate: null,
  gradesRemaining: null,
  cancelAtPeriodEnd: false,
  cancelAtDate: null,
});
const isLoadingSubscription = ref(false);
const subscriptionError = ref('');
const isCancelling = ref(false);

// --- Calendar Data ---
const isGoogleConnected = ref(false);
const connectedCalendars = ref([]);
const isLoadingCalendars = ref(false);
const calendarFetchError = ref('');

// --- Modals Refs ---
const cancelModal = ref(null);
let bsCancelModal = null;

// --- Computed User Data ---
const user = computed(() => store.state.user.user);

// --- Methods ---
function setActiveTab(tabName) {
  activeTab.value = tabName;
  router.replace({ hash: `#${tabName}` });
}

function clearGoogleMessages() {
  googleSuccessMessage.value = '';
  googleErrorMessage.value = '';
  router.replace({ query: {} });
}

// Fetch user data and populate form
function populateAccountForm() {
  if (user.value) {
    accountForm.name = user.value.name;
    accountForm.email = user.value.email;
  }
}

async function updateAccount() {
  isUpdatingAccount.value = true;
  successMessage.value = '';
  errorMessage.value = '';
  try {
    const response = await axios.put('/api/profile', accountForm);
    successMessage.value = response.data.message;
    store.commit('user/SET_USER', response.data.user);
    populateAccountForm();
  } catch (error) {
    console.error('Error updating account:', error);
    if (error.response && error.response.data) {
      errorMessage.value = error.response.data.message || 'Failed to update account.';
      if (error.response.status === 422 && error.response.data.errors) {
        errorMessage.value = Object.values(error.response.data.errors).flat().join(' ');
      }
    } else {
      errorMessage.value = 'An unexpected error occurred.';
    }
  } finally {
    isUpdatingAccount.value = false;
  }
}

async function updatePassword() {
  isUpdatingPassword.value = true;
  successMessage.value = '';
  errorMessage.value = '';
  try {
    const response = await axios.put('/api/password', passwordForm);
    successMessage.value = response.data.message;
    passwordForm.current_password = '';
    passwordForm.password = '';
    passwordForm.password_confirmation = '';
  } catch (error) {
    console.error('Error updating password:', error);
     if (error.response && error.response.data) {
      errorMessage.value = error.response.data.message || 'Failed to update password.';
      if (error.response.status === 422 && error.response.data.errors) {
        errorMessage.value = Object.values(error.response.data.errors).flat().join(' ');
      }
    } else {
      errorMessage.value = 'An unexpected error occurred.';
    }
  } finally {
    isUpdatingPassword.value = false;
  }
}

// Fetch Notification Settings
async function fetchNotificationSettings() {
  errorMessage.value = ''; // Clear previous errors
  try {
    const response = await axios.get('/api/notifications/settings');
    // Update the reactive form object directly
    Object.assign(notificationForm, response.data);
    console.log('Fetched notification settings:', JSON.parse(JSON.stringify(notificationForm))); // Log fetched data
  } catch (error) {
    console.error('Error fetching notification settings:', error);
    // Don't overwrite other error messages, maybe show a specific notification error?
    errorMessage.value = 'Failed to load notification settings. Default values may be shown.';
  }
}

// Update Notification Settings
async function updateNotifications() {
  isUpdatingNotifications.value = true;
  successMessage.value = '';
  errorMessage.value = '';
  try {
    console.log('Saving notification settings:', JSON.parse(JSON.stringify(notificationForm))); // Log data being sent
    const response = await axios.put('/api/notifications/settings', notificationForm);
    successMessage.value = response.data.message;
  } catch (error) {
    console.error('Error updating notification settings:', error);
    if (error.response && error.response.data) {
      errorMessage.value = error.response.data.message || 'Failed to save notification settings.';
      if (error.response.status === 422 && error.response.data.errors) {
        errorMessage.value = Object.values(error.response.data.errors).flat().join(' ');
      }
    } else {
      errorMessage.value = 'An unexpected error occurred while saving notification settings.';
    }
  } finally {
    isUpdatingNotifications.value = false;
  }
}

// Fetch Subscription Status
async function fetchSubscriptionStatus() {
  isLoadingSubscription.value = true;
  subscriptionError.value = '';
  try {
    const response = await axios.get('/api/subscription/status');
    Object.assign(subscriptionStatus, response.data);
    console.log('Fetched subscription status:', JSON.parse(JSON.stringify(subscriptionStatus)));
  } catch (error) {
    console.error('Error fetching subscription status:', error);
    subscriptionError.value = 'Failed to load subscription status.';
  } finally {
    isLoadingSubscription.value = false;
  }
}

function openCancelModal() { if (bsCancelModal) bsCancelModal.show(); }
function closeCancelModal() { if (bsCancelModal) bsCancelModal.hide(); }

async function confirmCancelSubscription() {
  isCancelling.value = true;
  errorMessage.value = '';
  successMessage.value = '';
  try {
    const response = await axios.post('/api/subscription/cancel');
    successMessage.value = response.data.message || 'Subscription cancelled successfully.';
    await fetchSubscriptionStatus();
    closeCancelModal();
  } catch (error) {
    console.error('Failed to cancel subscription:', error);
    errorMessage.value = error.response?.data?.message || 'Failed to cancel subscription.';
  } finally {
    isCancelling.value = false;
  }
}

async function connectGoogleCalendar() {
  try {
    const response = await axios.get('/api/calendars/auth');
    window.location.href = response.data.authUrl;
  } catch (error) {
    console.error('Error getting Google auth URL:', error);
    errorMessage.value = 'Failed to initiate Google Calendar connection.';
  }
}

async function disconnectGoogleCalendar() {
  try {
    await axios.post('/api/calendars/disconnect-all');
    isGoogleConnected.value = false;
    connectedCalendars.value = [];
    googleSuccessMessage.value = 'Google Calendar disconnected successfully.';
  } catch (error) {
    console.error('Error disconnecting Google Calendar:', error);
    errorMessage.value = 'Failed to disconnect Google Calendar.';
  }
}

async function fetchGoogleData() {
  isLoadingCalendars.value = true;
  calendarFetchError.value = '';
  try {
    const connectionStatusResponse = await axios.get('/api/calendars/check-connection');
    isGoogleConnected.value = connectionStatusResponse.data.isConnected;
    if (isGoogleConnected.value) {
      const calendarsResponse = await axios.get('/api/calendars');
      connectedCalendars.value = calendarsResponse.data;
    } else {
      connectedCalendars.value = [];
    }
  } catch (error) {
    console.error('Error fetching Google Calendar data:', error);
    calendarFetchError.value = 'Failed to load calendar data.';
  } finally {
    isLoadingCalendars.value = false;
  }
}

async function toggleCalendarSelection(calendar) {
  const newSelectionState = !calendar.is_selected;
  const originalState = calendar.is_selected;
  calendar.is_selected = newSelectionState;
  try {
    await axios.post('/api/calendars/selection', {
      calendarId: calendar.id,
      isSelected: newSelectionState
    });
  } catch (error) {
    console.error('Error updating calendar selection:', error);
    calendar.is_selected = originalState;
    errorMessage.value = `Failed to update selection for calendar '${calendar.summary}'.`;
  }
}

// --- Lifecycle Hooks ---
onMounted(async () => { // Make onMounted async
  console.log('Settings.vue mounted');
  if (cancelModal.value) {
    bsCancelModal = new Modal(cancelModal.value);
  }

  if (route.query.google_success) {
    googleSuccessMessage.value = route.query.google_success;
  }
  if (route.query.google_error) {
    googleErrorMessage.value = route.query.google_error;
  }

  if (route.hash) {
    const hashTab = route.hash.substring(1);
    const validTabs = ['account', 'notifications', 'subscription', 'calendar'];
    if (validTabs.includes(hashTab)) {
      activeTab.value = hashTab;
    }
  }

  // Populate form with initial user data
  populateAccountForm();

  // Fetch other settings data - await them to ensure loading completes
  await fetchNotificationSettings();
  await fetchSubscriptionStatus();
  await fetchGoogleData();
});

// Watch for route query changes
watch(() => route.query, (newQuery) => {
  if (newQuery.google_success) {
    googleSuccessMessage.value = newQuery.google_success;
    fetchGoogleData();
  }
  if (newQuery.google_error) {
    googleErrorMessage.value = newQuery.google_error;
  }
});

// Watch for changes in user data from Vuex store
watch(user, (newUser) => {
  if (newUser) {
    populateAccountForm();
  }
});

</script>

<style scoped>
/* Styles remain the same */
.page-container {
  padding-bottom: 2rem;
}

.page-heading {
  margin-bottom: 1.5rem;
  color: var(--dark-blue);
  font-weight: 600;
}

.settings-card {
  background-color: #fff;
  box-shadow: var(--shadow);
  border: none;
}

.settings-tabs {
  border-bottom: 1px solid #dee2e6;
  margin-bottom: 1rem;
}

.settings-tabs .nav-item {
  margin-bottom: -1px;
}

.settings-tabs .nav-link {
  border: none;
  border-top-left-radius: 0.375rem;
  border-top-right-radius: 0.375rem;
  color: #333;
  background-color: transparent;
  padding: 0.75rem 1.25rem;
  font-weight: 500;
  transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
}

.settings-tabs .nav-link:hover,
.settings-tabs .nav-link:focus {
  border-color: transparent;
  color: var(--primary-purple);
}

.settings-tabs .nav-link.active {
  color: var(--primary-purple);
  background-color: #fff;
  border-bottom: 2px solid var(--primary-purple);
  font-weight: 600;
}

.settings-tab-content {
  padding-top: 1rem;
}

.settings-tab-content h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--dark-blue);
    margin-bottom: 1rem;
}

.subscription-status {
  display: flex;
  align-items: center;
  padding: 1rem;
  border-radius: 0.375rem;
}

.subscription-status.active {
  background-color: #e9f5e9;
  border: 1px solid #a5d6a7;
}

.subscription-status.inactive {
  background-color: #e3f2fd;
  border: 1px solid #90caf9;
}

.status-icon {
  font-size: 1.5rem;
  margin-right: 1rem;
}

.subscription-status.active .status-icon {
  color: #4caf50;
}

.subscription-status.inactive .status-icon {
  color: #2196f3;
}

.status-details h4 {
  margin-bottom: 0.25rem;
}

.status-details p {
  margin-bottom: 0;
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

.connected-calendars {
  margin-top: 2rem;
}

.list-group-item span[style*="color"] {
  display: inline-block;
  width: 1em;
  height: 1em;
  margin-right: 0.5em;
  vertical-align: middle;
  border: 1px solid rgba(0,0,0,0.2);
}
</style>
