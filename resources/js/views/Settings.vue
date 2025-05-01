<template>
  <div class="container page-container">
    <!-- Page Heading -->
    <h1 class="page-heading">Settings</h1>

    <div class="row justify-content-center">
      <div class="col-md-10">
        <!-- Alert Messages for Google Calendar Callback -->
        <div v-if="googleSuccessMessage" class="alert alert-success alert-dismissible fade show" role="alert">
          {{ googleSuccessMessage }}
          <button type="button" class="btn-close" @click="clearGoogleMessages" aria-label="Close"></button>
        </div>
        <div v-if="googleErrorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
          {{ googleErrorMessage }}
          <button type="button" class="btn-close" @click="clearGoogleMessages" aria-label="Close"></button>
        </div>
        <!-- Placeholder for other general success/error messages -->
        <div v-if="generalErrorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
          {{ generalErrorMessage }}
          <button type="button" class="btn-close" @click="generalErrorMessage = ''" aria-label="Close"></button>
        </div>

        <div class="card settings-card">
          <!-- Card Header removed, heading is now above -->
          <div class="card-body">
            <ul class="nav nav-tabs settings-tabs" id="settingsTabs" role="tablist">
              <!-- Tabs remain the same -->
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
                <!-- Account form remains the same -->
                <h3>Account Information</h3>
                <form @submit.prevent="updateAccount">
                  <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" v-model="accountForm.name">
                  </div>
                  <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" v-model="accountForm.email">
                  </div>
                  <button type="submit" class="btn btn-primary">Update Account</button>
                </form>

                <hr class="my-4">

                <h3>Change Password</h3>
                <form @submit.prevent="updatePassword">
                  <div class="mb-3">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" class="form-control" id="current_password" v-model="passwordForm.current_password">
                  </div>
                  <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="password" v-model="passwordForm.password">
                  </div>
                  <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="password_confirmation" v-model="passwordForm.password_confirmation">
                  </div>
                  <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
              </div>

              <!-- Notification Settings -->
              <div class="tab-pane fade" :class="{ 'show active': activeTab === 'notifications' }">
                <!-- Notifications form remains the same -->
                <h3>Notification Preferences</h3>
                <form @submit.prevent="updateNotifications">
                  <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="weekly_grade_email" v-model="notificationForm.weekly_grade_email">
                    <label class="form-check-label" for="weekly_grade_email">Send me weekly grade emails</label>
                  </div>
                  <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="planning_reminder" v-model="notificationForm.planning_reminder">
                    <label class="form-check-label" for="planning_reminder">Send me weekly planning reminders</label>
                  </div>
                  <div class="mb-3">
                    <label for="reminder_day" class="form-label">Reminder Day</label>
                    <select class="form-select" id="reminder_day" v-model="notificationForm.reminder_day">
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
                    <input type="time" class="form-control" id="reminder_time" v-model="notificationForm.reminder_time">
                  </div>
                  <button type="submit" class="btn btn-primary">Save Notification Settings</button>
                </form>
              </div>

              <!-- Subscription Settings -->
              <div class="tab-pane fade" :class="{ 'show active': activeTab === 'subscription' }">
                <!-- Subscription content remains the same -->
                <h3>Subscription Status</h3>
                <div class="card mb-4">
                  <div class="card-body">
                    <div v-if="isSubscribed" class="subscription-status active">
                      <div class="status-icon"><i class="fas fa-check-circle"></i></div>
                      <div class="status-details">
                        <h4>Premium Plan</h4>
                        <p>Your subscription is active.</p>
                        <p class="text-muted">Next billing date: {{ nextBillingDate }}</p>
                      </div>
                    </div>
                    <div v-else class="subscription-status inactive">
                      <div class="status-icon"><i class="fas fa-info-circle"></i></div>
                      <div class="status-details">
                        <h4>Free Plan</h4>
                        <p>You are currently on the free plan.</p>
                        <p class="text-muted">{{ gradesRemaining }} grades remaining</p>
                      </div>
                    </div>

                    <div class="mt-4">
                      <button v-if="isSubscribed" type="button" class="btn btn-outline-danger" @click="openCancelModal">
                        Cancel Subscription
                      </button>
                      <router-link v-else to="/subscription" class="btn btn-primary">Upgrade to Premium</router-link>
                    </div>
                  </div>
                </div>

                <div v-if="isSubscribed" class="card">
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
                  <!-- Display loading state -->
                  <div v-if="isLoadingCalendars" class="text-center my-3">
                    <div class="spinner-border text-primary" role="status">
                      <span class="visually-hidden">Loading calendars...</span>
                    </div>
                  </div>
                  <!-- Display error state -->
                  <div v-else-if="calendarFetchError" class="alert alert-warning">
                    {{ calendarFetchError }}
                  </div>
                  <!-- Display no calendars connected -->
                  <div v-else-if="!isGoogleConnected || connectedCalendars.length === 0" class="alert alert-info">
                    No calendars connected yet. Click the "Connect Calendar" button to get started.
                  </div>
                  <!-- Display connected calendars list with checkboxes -->
                  <ul v-else class="list-group">
                    <li v-for="calendar in connectedCalendars" :key="calendar.id" class="list-group-item d-flex justify-content-between align-items-center">
                      <div>
                        <span :style="{ color: calendar.backgroundColor }">■</span> <!-- Simple color indicator -->
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
      <!-- Modal content remains the same -->
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
import { ref, reactive, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios'; // Import axios for API calls
import { Modal } from 'bootstrap'; // Import Modal for programmatic control

const route = useRoute();
const router = useRouter();

const activeTab = ref('account'); // Default tab

// --- Google Callback Messages ---
const googleSuccessMessage = ref('');
const googleErrorMessage = ref('');
const generalErrorMessage = ref(''); // For other errors like selection update

// --- Form Data ---
const accountForm = reactive({ name: '', email: '' });
const passwordForm = reactive({ current_password: '', password: '', password_confirmation: '' });
const notificationForm = reactive({
  weekly_grade_email: true,
  planning_reminder: true,
  reminder_day: 'Sunday',
  reminder_time: '18:00'
});

// --- Subscription Data ---
const isSubscribed = ref(true); // Placeholder
const nextBillingDate = ref('May 30, 2025'); // Placeholder
const gradesRemaining = ref(0); // Placeholder
const isCancelling = ref(false);

// --- Calendar Data ---
const isGoogleConnected = ref(false); 
const connectedCalendars = ref([]); 
const isLoadingCalendars = ref(false);
const calendarFetchError = ref('');

// --- Modals Refs ---
const cancelModal = ref(null);
let bsCancelModal = null;

// --- Methods ---
function setActiveTab(tabName) {
  activeTab.value = tabName;
  router.replace({ hash: `#${tabName}` });
}

function clearGoogleMessages() {
  googleSuccessMessage.value = '';
  googleErrorMessage.value = '';
  // Remove query params from URL without reloading
  router.replace({ query: {} }); 
}

async function updateAccount() { /* ... API call ... */ }
async function updatePassword() { /* ... API call ... */ }
async function updateNotifications() { /* ... API call ... */ }

function openCancelModal() { if (bsCancelModal) bsCancelModal.show(); }
function closeCancelModal() { if (bsCancelModal) bsCancelModal.hide(); }

async function confirmCancelSubscription() {
  isCancelling.value = true;
  try {
    // TODO: Call API to cancel subscription
    await new Promise(resolve => setTimeout(resolve, 1500));
    isSubscribed.value = false;
    closeCancelModal();
  } catch (error) {
    console.error('Failed to cancel subscription:', error);
    // TODO: Show error message to user
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
    generalErrorMessage.value = 'Failed to initiate Google Calendar connection. Please try again.';
  }
}

async function disconnectGoogleCalendar() {
  try {
    // Assuming a single disconnect endpoint for simplicity now
    await axios.post('/api/calendars/disconnect-all'); 
    isGoogleConnected.value = false;
    connectedCalendars.value = [];
    googleSuccessMessage.value = 'Google Calendar disconnected successfully.';
  } catch (error) {
    console.error('Error disconnecting Google Calendar:', error);
    generalErrorMessage.value = 'Failed to disconnect Google Calendar. Please try again.';
  }
}

async function fetchGoogleData() {
  isLoadingCalendars.value = true;
  calendarFetchError.value = '';
  try {
    console.log('Settings.vue: Fetching Google connection status...');
    const connectionStatusResponse = await axios.get('/api/calendars/check-connection');
    isGoogleConnected.value = connectionStatusResponse.data.isConnected;
    console.log('Settings.vue: Connection status:', isGoogleConnected.value);

    if (isGoogleConnected.value) {
      console.log('Settings.vue: Fetching connected calendars...');
      const calendarsResponse = await axios.get('/api/calendars');
      connectedCalendars.value = calendarsResponse.data;
      console.log('Settings.vue: Fetched calendars:', connectedCalendars.value);
    } else {
      connectedCalendars.value = [];
      console.log('Settings.vue: Google not connected, clearing calendars.');
    }
  } catch (error) {
    console.error('Error fetching Google Calendar data:', error);
    calendarFetchError.value = 'Failed to load calendar data. Please try reconnecting your calendar or refresh the page.';
    // Don't assume disconnected on error, could be temporary API issue
    // isGoogleConnected.value = false; 
    // connectedCalendars.value = [];
  } finally {
    isLoadingCalendars.value = false;
  }
}

async function toggleCalendarSelection(calendar) {
  const newSelectionState = !calendar.is_selected;
  console.log(`Toggling calendar ${calendar.id} (${calendar.summary}) selection to ${newSelectionState}`);
  
  // Optimistically update UI
  const originalState = calendar.is_selected;
  calendar.is_selected = newSelectionState;

  try {
    await axios.post('/api/calendars/selection', {
      calendarId: calendar.id,
      isSelected: newSelectionState
    });
    console.log(`Successfully updated selection for calendar ${calendar.id}`);
  } catch (error) {
    console.error('Error updating calendar selection:', error);
    // Revert optimistic update on error
    calendar.is_selected = originalState;
    generalErrorMessage.value = `Failed to update selection for calendar '${calendar.summary}'. Please try again.`;
  }
}

// --- Lifecycle Hooks ---
onMounted(() => {
  console.log('Settings.vue mounted');
  // Initialize Bootstrap modal
  if (cancelModal.value) {
    bsCancelModal = new Modal(cancelModal.value);
  }

  // Check for Google callback query parameters
  if (route.query.google_success) {
    googleSuccessMessage.value = route.query.google_success;
  }
  if (route.query.google_error) {
    googleErrorMessage.value = route.query.google_error;
  }

  // Set active tab based on hash
  if (route.hash) {
    const hashTab = route.hash.substring(1);
    const validTabs = ['account', 'notifications', 'subscription', 'calendar'];
    if (validTabs.includes(hashTab)) {
      activeTab.value = hashTab;
    }
  }

  // Fetch initial data
  fetchGoogleData();
  // TODO: Fetch account, notification, subscription data
});

// Watch for route query changes (e.g., after Google redirect)
watch(() => route.query, (newQuery) => {
  if (newQuery.google_success) {
    googleSuccessMessage.value = newQuery.google_success;
    fetchGoogleData(); // Re-fetch data after successful connection
  }
  if (newQuery.google_error) {
    googleErrorMessage.value = newQuery.google_error;
  }
});

</script>

<style scoped>
/* Add custom styles if needed */
.page-container {
  /* Removed padding-top to rely on main-content padding */
  padding-bottom: 2rem;
}

.page-heading {
  margin-bottom: 1.5rem; /* Space below heading */
  color: var(--dark-blue); /* Use consistent heading color */
  font-weight: 600; /* Make heading bolder */
}

.settings-card {
  /* Add any specific card styling if needed */
  background-color: #fff; /* Ensure card background is white */
  box-shadow: var(--shadow);
  border: none; /* Remove default border if any */
}

.settings-tabs {
  border-bottom: 1px solid #dee2e6; /* Standard Bootstrap border */
  margin-bottom: 1rem; /* Space below tabs */
}

.settings-tabs .nav-item {
  margin-bottom: -1px; /* Align bottom border */
}

.settings-tabs .nav-link {
  border: none; /* Remove individual borders */
  border-top-left-radius: 0.375rem; /* Match card radius */
  border-top-right-radius: 0.375rem;
  color: #333; /* Changed from var(--dark-blue) to explicit dark grey */
  background-color: transparent;
  padding: 0.75rem 1.25rem; /* Adjust padding */
  font-weight: 500;
  transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
}

.settings-tabs .nav-link:hover,
.settings-tabs .nav-link:focus {
  border-color: transparent; /* No border on hover/focus */
  color: var(--primary-purple); /* Highlight color on hover */
}

.settings-tabs .nav-link.active {
  color: var(--primary-purple); /* Active tab color */
  background-color: #fff; /* Ensure active tab background matches card */
  border-bottom: 2px solid var(--primary-purple); /* Add bottom border for active state */
  font-weight: 600;
}

.settings-tab-content {
  /* Add padding or other styles for the content area */
  padding-top: 1rem; /* Add some space above content */
}

.settings-tab-content h3 {
    font-size: 1.25rem; /* Slightly smaller headings within tabs */
    font-weight: 600;
    color: var(--dark-blue);
    margin-bottom: 1rem;
}

.subscription-status {
  display: flex;
  align-items: center;
  padding: 1rem;
  border-radius: 0.375rem; /* Bootstrap's default */
}

.subscription-status.active {
  background-color: #e9f5e9; /* Light green background */
  border: 1px solid #a5d6a7; /* Green border */
}

.subscription-status.inactive {
  background-color: #e3f2fd; /* Light blue background */
  border: 1px solid #90caf9; /* Blue border */
}

.status-icon {
  font-size: 1.5rem;
  margin-right: 1rem;
}

.subscription-status.active .status-icon {
  color: #4caf50; /* Green icon */
}

.subscription-status.inactive .status-icon {
  color: #2196f3; /* Blue icon */
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
