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
                    <div v-if="!isLoadingTimezones && timezones.length === 0" class="form-text text-danger">Failed to load timezones. Please refresh.</div>
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

                <hr class="my-4">

                <!-- Onboarding Profile Section -->
                <h3>Your Profile</h3>
                <p class="text-muted">This information helps the AI understand your priorities.</p>
                <OnboardingForm @onboarding-complete="handleProfileUpdateSuccess" />

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
              {{ isCancelling ? 'Cancelling...' : 'Confirm Cancellation' }}
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useStore } from 'vuex';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import { Modal } from 'bootstrap'; // Import Modal for programmatic control
import OnboardingForm from '../components/OnboardingForm.vue'; // Import the onboarding form

const store = useStore();
const router = useRouter();
const route = useRoute();

// --- Reactive State --- 
const activeTab = ref('account'); // Default active tab
const successMessage = ref('');
const errorMessage = ref('');
const googleSuccessMessage = ref('');
const googleErrorMessage = ref('');

// Account Form
const accountForm = reactive({
  name: '',
  email: '',
  timezone: '',
});
const isUpdatingAccount = ref(false);
const timezones = ref([]);
const isLoadingTimezones = ref(false);

// Password Form
const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
});
const isUpdatingPassword = ref(false);

// Notification Form
const notificationForm = reactive({
  weekly_grade_email: false,
  planning_reminder: false,
  reminder_day: 'Sunday',
  reminder_time: '09:00',
});
const isUpdatingNotifications = ref(false);

// Subscription
const isLoadingSubscription = ref(false);
const subscriptionStatus = ref({});
const subscriptionError = ref('');
const cancelModal = ref(null);
let bsCancelModal = null;
const isCancelling = ref(false);

// Calendar Integration
const isGoogleConnected = computed(() => store.state.user.user?.google_calendar_connected || false);
const connectedCalendars = ref([]);
const isLoadingCalendars = ref(false);
const calendarFetchError = ref('');

// --- Computed Properties ---
const user = computed(() => store.state.user.user);

// --- Methods --- 

// Tab Navigation
function setActiveTab(tabName) {
  activeTab.value = tabName;
  router.replace({ hash: `#${tabName}` }); // Update URL hash without adding history
  // Fetch data specific to the tab if needed
  if (tabName === 'subscription') {
    fetchSubscriptionStatus();
  } else if (tabName === 'calendar') {
    fetchConnectedCalendars();
  } else if (tabName === 'account') {
    fetchTimezones(); // Fetch timezones when account tab is active
  }
}

// Account Management
async function fetchTimezones() {
  if (timezones.value.length > 0) return; // Don't refetch if already loaded
  isLoadingTimezones.value = true;
  console.log('[Settings] Fetching timezones...'); // Added log
  try {
    const response = await axios.get('/api/timezones');
    console.log('[Settings] Received timezone response:', response); // Added log
    if (response.data && Array.isArray(response.data.timezones)) {
        timezones.value = response.data.timezones;
        console.log('[Settings] Assigned timezones:', timezones.value); // Added log
    } else {
        console.error('[Settings] Invalid timezone data received:', response.data); // Added log
        errorMessage.value = 'Failed to load timezones: Invalid data format.';
        timezones.value = []; // Ensure it's empty on error
    }
  } catch (error) {
    console.error('[Settings] Error fetching timezones:', error); // Added log
    errorMessage.value = 'Failed to load timezones. Please try refreshing the page.';
    timezones.value = []; // Ensure it's empty on error
  } finally {
    isLoadingTimezones.value = false;
    console.log('[Settings] Finished fetching timezones. Loading:', isLoadingTimezones.value, 'Count:', timezones.value.length); // Added log
  }
}

async function updateAccount() {
  isUpdatingAccount.value = true;
  successMessage.value = '';
  errorMessage.value = '';
  try {
    await store.dispatch('user/updateProfile', accountForm);
    successMessage.value = 'Account updated successfully!';
  } catch (error) {
    errorMessage.value = error.message || 'Failed to update account.';
  } finally {
    isUpdatingAccount.value = false;
  }
}

async function updatePassword() {
  isUpdatingPassword.value = true;
  successMessage.value = '';
  errorMessage.value = '';
  try {
    // Use the correct endpoint defined in api.php
    await axios.put('/api/password', passwordForm); 
    successMessage.value = 'Password updated successfully!';
    // Clear password fields
    passwordForm.current_password = '';
    passwordForm.password = '';
    passwordForm.password_confirmation = '';
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Failed to update password.';
    console.error("Password update error:", error.response?.data);
  } finally {
    isUpdatingPassword.value = false;
  }
}

// Onboarding Profile Update Success Handler
function handleProfileUpdateSuccess() {
    successMessage.value = 'Profile updated successfully!';
    // Optionally clear error message if it was set by the form
    errorMessage.value = ''; 
}

// Notification Management
async function fetchNotifications() {
  try {
    const response = await axios.get('/api/notifications/settings');
    const settings = response.data;
    notificationForm.weekly_grade_email = settings.weekly_grade_email || false;
    notificationForm.planning_reminder = settings.planning_reminder || false;
    notificationForm.reminder_day = settings.reminder_day || 'Sunday';
    notificationForm.reminder_time = settings.reminder_time || '09:00';
  } catch (error) {
    console.error('Error fetching notification settings:', error);
    errorMessage.value = 'Could not load notification settings.';
  }
}

async function updateNotifications() {
  isUpdatingNotifications.value = true;
  successMessage.value = '';
  errorMessage.value = '';
  try {
    // Use PUT as defined in api.php
    await axios.put('/api/notifications/settings', notificationForm); 
    successMessage.value = 'Notification settings saved!';
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Failed to save notification settings.';
    console.error('Error saving notification settings:', error.response?.data);
  } finally {
    isUpdatingNotifications.value = false;
  }
}

// Subscription Management
async function fetchSubscriptionStatus() {
  isLoadingSubscription.value = true;
  subscriptionError.value = '';
  try {
    const response = await axios.get('/api/subscription/status');
    subscriptionStatus.value = response.data;
  } catch (error) {
    console.error('Error fetching subscription status:', error);
    subscriptionError.value = 'Could not load subscription status. Please try again later.';
  } finally {
    isLoadingSubscription.value = false;
  }
}

function openCancelModal() {
  if (!bsCancelModal) {
    bsCancelModal = new Modal(cancelModal.value);
  }
  bsCancelModal.show();
}

function closeCancelModal() {
  if (bsCancelModal) {
    bsCancelModal.hide();
  }
}

async function confirmCancelSubscription() {
  isCancelling.value = true;
  successMessage.value = '';
  errorMessage.value = '';
  try {
    await axios.post('/api/subscription/cancel');
    successMessage.value = 'Subscription cancelled successfully. You will retain access until the end of your billing period.';
    await fetchSubscriptionStatus(); // Refresh status
    closeCancelModal();
  } catch (error) {
    errorMessage.value = error.response?.data?.error || 'Failed to cancel subscription.';
    console.error('Error cancelling subscription:', error.response?.data);
  } finally {
    isCancelling.value = false;
  }
}

// Calendar Integration Management
function connectGoogleCalendar() {
  // Redirect to backend route which initiates Google OAuth flow
  window.location.href = '/auth/google/redirect';
}

async function disconnectGoogleCalendar() {
  googleSuccessMessage.value = '';
  googleErrorMessage.value = '';
  try {
    // Use the correct endpoint defined in api.php
    await axios.post('/api/calendars/disconnect-all'); 
    await store.dispatch('user/fetchUser'); // Refresh user state
    googleSuccessMessage.value = 'All Google Calendars disconnected successfully.';
    connectedCalendars.value = []; // Clear local calendar list
  } catch (error) {
    googleErrorMessage.value = error.response?.data?.error || 'Failed to disconnect Google Calendars.';
    console.error('Error disconnecting Google Calendars:', error.response?.data);
  }
}

async function fetchConnectedCalendars() {
  if (!isGoogleConnected.value) {
    connectedCalendars.value = [];
    return;
  }
  isLoadingCalendars.value = true;
  calendarFetchError.value = '';
  try {
    // Use the correct endpoint defined in api.php
    const response = await axios.get('/api/calendars'); 
    connectedCalendars.value = response.data.calendars || [];
  } catch (error) {
    console.error('Error fetching connected calendars:', error);
    calendarFetchError.value = 'Could not load your calendars. Please try reconnecting your Google Account or refresh the page.';
    connectedCalendars.value = [];
  } finally {
    isLoadingCalendars.value = false;
  }
}

async function toggleCalendarSelection(calendar) {
  const newSelectedState = !calendar.is_selected;
  // Optimistically update UI
  calendar.is_selected = newSelectedState;

  try {
    // Use the correct endpoint defined in api.php
    await axios.post('/api/calendars/selection', { 
      calendar_id: calendar.calendar_id, // Use calendar_id from the data
      selected: newSelectedState,
    });
    // Success - UI already updated
    googleSuccessMessage.value = `Calendar '${calendar.summary}' ${newSelectedState ? 'included' : 'excluded'}.`;
    googleErrorMessage.value = ''; // Clear any previous error
  } catch (error) {
    // Revert UI on error
    calendar.is_selected = !newSelectedState;
    googleErrorMessage.value = `Failed to update selection for '${calendar.summary}'. Please try again.`;
    googleSuccessMessage.value = ''; // Clear success message
    console.error('Error toggling calendar selection:', error.response?.data);
  }
}

function clearGoogleMessages() {
    googleSuccessMessage.value = '';
    googleErrorMessage.value = '';
}

// --- Lifecycle Hooks --- 
onMounted(async () => {
  // Ensure user data is loaded
  if (!user.value) {
    await store.dispatch('user/fetchUser');
  }
  // Populate forms with user data
  if (user.value) {
    accountForm.name = user.value.name;
    accountForm.email = user.value.email;
    accountForm.timezone = user.value.timezone || '';
  }

  // Handle Google OAuth callback messages
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('google_success')) {
    googleSuccessMessage.value = urlParams.get('google_success');
    setActiveTab('calendar'); // Switch to calendar tab on success
    // Clean up URL params
    router.replace({ query: {} });
  }
  if (urlParams.has('google_error')) {
    googleErrorMessage.value = urlParams.get('google_error');
    setActiveTab('calendar'); // Switch to calendar tab on error
    // Clean up URL params
    router.replace({ query: {} });
  }

  // Set active tab based on URL hash or default
  const hash = route.hash.substring(1);
  if (['account', 'notifications', 'subscription', 'calendar'].includes(hash)) {
    setActiveTab(hash);
  } else {
    setActiveTab('account'); // Default to account
  }

  // Initial data fetch for relevant tabs
  fetchNotifications(); // Fetch notification settings regardless of initial tab
  if (activeTab.value === 'subscription') {
    fetchSubscriptionStatus();
  }
  if (activeTab.value === 'calendar') {
    fetchConnectedCalendars();
  }
  if (activeTab.value === 'account') {
    fetchTimezones(); // Fetch timezones if starting on account tab
  }

  // Initialize cancel modal instance
  if (cancelModal.value) {
    bsCancelModal = new Modal(cancelModal.value);
  }
});

// Watch for changes in user data (e.g., after login/refresh)
watch(user, (newUser) => {
  if (newUser) {
    accountForm.name = newUser.name;
    accountForm.email = newUser.email;
    accountForm.timezone = newUser.timezone || '';
    // Refetch calendars if connection status changes
    if (activeTab.value === 'calendar') {
        fetchConnectedCalendars();
    }
  }
});

</script>

<style scoped>
.page-container {
  padding-top: 2rem;
  padding-bottom: 4rem;
}

.page-heading {
  margin-bottom: 2rem;
  text-align: center;
}

.settings-card {
  border: none;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.settings-tabs {
  border-bottom: 1px solid #dee2e6;
}

.settings-tabs .nav-item .nav-link {
  color: #495057;
  border: none;
  border-bottom: 3px solid transparent;
  padding: 0.75rem 1rem;
  font-weight: 500;
}

.settings-tabs .nav-item .nav-link.active {
  color: var(--primary-blue);
  border-bottom-color: var(--primary-blue);
}

.settings-tab-content {
  padding: 1.5rem;
}

.settings-tab-content h3 {
  margin-bottom: 1.5rem;
  color: var(--primary-dark-blue);
}

.form-label {
  font-weight: 500;
}

/* Reduce spacing for onboarding form within settings */
.settings-tab-content .onboarding-form-container {
    max-width: none; /* Remove max-width */
    margin: 0; /* Remove margin */
    padding: 0; /* Remove padding */
    box-shadow: none; /* Remove shadow */
    background-color: transparent; /* Remove background */
}

.settings-tab-content .onboarding-form-container h2,
.settings-tab-content .onboarding-form-container p {
    display: none; /* Hide the welcome text in settings */
}

.subscription-status {
  display: flex;
  align-items: center;
  padding: 1rem;
  border-radius: 0.375rem;
}

.subscription-status.active {
  background-color: #e6f7ff; /* Light blue */
  border: 1px solid #91d5ff; /* Lighter blue border */
}

.subscription-status.inactive {
  background-color: #f8f9fa; /* Light gray */
  border: 1px solid #dee2e6;
}

.status-icon {
  font-size: 1.8rem;
  margin-right: 1rem;
}

.subscription-status.active .status-icon {
  color: var(--primary-blue);
}

.subscription-status.inactive .status-icon {
  color: #6c757d; /* Gray */
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
  padding: 0.75rem 1rem;
}

.connected-calendars .form-check-input {
  cursor: pointer;
}

</style>

